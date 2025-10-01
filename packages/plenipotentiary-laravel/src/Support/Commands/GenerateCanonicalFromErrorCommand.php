<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Support\Commands;

use Illuminate\Console\Command;
use JsonException;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Support\CanonicalFactory;
use Plenipotentiary\Laravel\Support\InputSource\ArraySource;

final class GenerateCanonicalFromErrorCommand extends Command
{
    protected $signature = 'pleni:generate-canonical '
        .' {--error= : JSON string or file containing the error payload }'
        .' {--operation=Plenipotentiary\\Laravel\\Pleni\\Google\\Ads\\Contexts\\Search\\Campaign\\Adapter\\CampaignCreate : Fully qualified operation class }'
        .' {--dto=Plenipotentiary\\Laravel\\Pleni\\Google\\Ads\\Contexts\\Search\\Campaign\\DTO\\CampaignCanonicalDTO : Fully qualified DTO class }'
        .' {--pretty : Output the DTO array as formatted PHP}';

    protected $description = 'Build a canonical DTO payload from an operation error response.';

    public function handle(): int
    {
        $errorPayload = $this->resolveErrorPayload();
        if (! $errorPayload) {
            $this->error('Unable to read error payload. Provide valid JSON or a path to a JSON file.');

            return self::FAILURE;
        }

        $expected = $errorPayload['expected']
            ?? ($errorPayload['payload']['expected'] ?? null);

        if (! is_array($expected)) {
            $this->error('Error payload does not contain an "expected" structure.');

            return self::FAILURE;
        }

        $operationClass = (string) $this->option('operation');
        if (! class_exists($operationClass)) {
            $this->error(sprintf('Operation class [%s] was not found.', $operationClass));

            return self::FAILURE;
        }

        $specStructure = $this->resolveOperationSpecStructure($operationClass);
        [$payload, $warnings] = $this->buildDtoPayload($expected, $specStructure, $operationClass);

        foreach ($warnings as $warning) {
            $this->warn($warning);
        }

        $dtoClass = $this->option('dto');
        if (! class_exists($dtoClass)) {
            $this->error(sprintf('DTO class [%s] was not found.', $dtoClass));

            return self::FAILURE;
        }

        $dto = $this->generateDto($dtoClass, $payload);
        if (! $dto) {
            $this->error('Unable to construct DTO with generated payload.');

            return self::FAILURE;
        }

        if ($this->option('pretty')) {
            $this->line($this->formatPhpArray($dto->toArray()));
        } else {
            $this->outputCanonicalSummary($dto);
        }

        return self::SUCCESS;
    }

    private function resolveErrorPayload(): ?array
    {
        $errorOption = $this->option('error');
        $content = null;

        if ($errorOption) {
            if (is_file($errorOption)) {
                $content = file_get_contents($errorOption) ?: null;
            } else {
                $content = $errorOption;
            }
        } else {
            $this->info('Paste the error JSON payload. Finish with CTRL+D (Linux/Mac) or CTRL+Z then Enter (Windows).');
            $content = stream_get_contents(STDIN) ?: null;
        }

        if (! $content) {
            return null;
        }

        try {
            $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error('Invalid JSON payload: '.$exception->getMessage());

            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function buildDtoPayload(array $expected, array $specStructure, string $operationClass): array
    {
        $fields = $expected['dto']['fields'] ?? [];
        $context = $expected['dto']['providerContext'] ?? [];

        $warnings = [];

        $fields = $this->mergeDefinitions($fields, $specStructure['fields'] ?? [], $operationClass, 'field', $warnings);
        $context = $this->mergeDefinitions($context, $specStructure['providerContext'] ?? [], $operationClass, 'provider context', $warnings);

        $payload = [];
        foreach ($fields as $field => $definition) {
            $required = (bool) ($definition['required'] ?? false);

            if (! $required && ! array_key_exists('default', $definition)) {
                continue;
            }

            $payload[$field] = array_key_exists('default', $definition)
                ? $definition['default']
                : $this->placeholderValue($definition);
        }

        $providerContext = [];
        foreach ($context as $key => $definition) {
            $required = (bool) ($definition['required'] ?? false);

            if (! $required && ! array_key_exists('default', $definition)) {
                continue;
            }

            $providerContext[$key] = array_key_exists('default', $definition)
                ? $definition['default']
                : $this->placeholderValue($definition);
        }

        if ($providerContext) {
            $payload['providerContext'] = $providerContext;
        }

        return [$payload, $warnings];
    }

    private function placeholderValue(array $definition): mixed
    {
        if (array_key_exists('default', $definition)) {
            return $definition['default'];
        }

        if (isset($definition['source']) && str_starts_with((string) $definition['source'], 'env:')) {
            $envKey = substr((string) $definition['source'], 4);

            return env($envKey) ?: sprintf('<%s>', $envKey);
        }

        $type = $definition['type'] ?? null;

        return match ($type) {
            'string' => '<string>',
            'enum' => $definition['values'][0] ?? '<enum>',
            'numeric', 'int' => $this->placeholderForCast($definition['cast'] ?? null),
            default => '<value>',
        };
    }

    private function placeholderForCast(?string $cast): mixed
    {
        return match ($cast) {
            'currency_to_micros' => 1_000_000,
            'int' => 0,
            'float' => 0.0,
            default => 0,
        };
    }

    private function resolveOperationSpecStructure(string $operationClass): array
    {
        $inputSpec = $this->extractInputSpec($operationClass);
        if ($inputSpec === null) {
            $this->warn(sprintf('Operation [%s] does not expose an INPUT_SPEC; proceeding with error payload only.', $operationClass));

            return ['fields' => [], 'providerContext' => []];
        }

        return $this->specToStructure($inputSpec);
    }

    private function extractInputSpec(string $operationClass): ?array
    {
        try {
            if (method_exists($operationClass, 'inputSpec')) {
                $spec = $operationClass::inputSpec();
            } elseif (defined(sprintf('%s::INPUT_SPEC', $operationClass))) {
                /** @var array $spec */
                $spec = $operationClass::INPUT_SPEC;
            } else {
                return null;
            }
        } catch (\Throwable $exception) {
            $this->warn(sprintf(
                'Unable to read INPUT_SPEC from [%s]: %s',
                $operationClass,
                $exception->getMessage()
            ));

            return null;
        }

        return is_array($spec) ? $spec : null;
    }

    private function specToStructure(array $inputSpec): array
    {
        $fields = [];
        $providerContext = [];

        foreach ($inputSpec as $key => $definition) {
            $descriptor = $this->descriptorFromSpec($definition);

            if (str_starts_with($key, 'providerContext.')) {
                $contextKey = substr($key, strlen('providerContext.'));
                $providerContext[$contextKey] = $descriptor;

                continue;
            }

            $fields[$key] = $descriptor;
        }

        return [
            'fields' => $fields,
            'providerContext' => $providerContext,
        ];
    }

    private function descriptorFromSpec(array $definition): array
    {
        $rules = $definition['rules'] ?? [];
        $descriptor = [
            'rules' => $rules,
            'required' => $this->isRuleRequired($rules),
        ];

        if (array_key_exists('default', $definition)) {
            $descriptor['default'] = $definition['default'];
        }

        if (array_key_exists('source', $definition)) {
            $descriptor['source'] = $definition['source'];
        }

        if (array_key_exists('cast', $definition)) {
            $descriptor['cast'] = $definition['cast'];
        }

        $type = $this->inferTypeFromRules($rules);
        if ($type !== null) {
            $descriptor['type'] = $type;
        }

        $enumValues = $this->extractEnumValues($rules);
        if ($enumValues) {
            $descriptor['values'] = $enumValues;
        }

        return $descriptor;
    }

    private function inferTypeFromRules(array $rules): ?string
    {
        foreach ($rules as $rule) {
            if (! is_string($rule)) {
                continue;
            }

            if ($rule === 'string') {
                return 'string';
            }

            if ($rule === 'numeric') {
                return 'numeric';
            }

            if (str_starts_with($rule, 'in:')) {
                return 'enum';
            }
        }

        return null;
    }

    private function extractEnumValues(array $rules): array
    {
        foreach ($rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'in:')) {
                $values = substr($rule, 3);

                return $values !== '' ? explode(',', $values) : [];
            }
        }

        return [];
    }

    private function isRuleRequired(array $rules): bool
    {
        foreach ($rules as $rule) {
            if (! is_string($rule)) {
                continue;
            }

            if (str_starts_with($rule, 'required')) {
                return true;
            }
        }

        return false;
    }

    private function mergeDefinitions(array $definitionSet, array $specDefinitions, string $operationClass, string $section, array &$warnings): array
    {
        foreach ($specDefinitions as $key => $definition) {
            if (! array_key_exists($key, $definitionSet)) {
                $definitionSet[$key] = $definition;

                if (! empty($definition['required'])) {
                    $warnings[] = sprintf(
                        'Missing required %s "%s" in error payload; inferred from %s::INPUT_SPEC.',
                        $section,
                        $key,
                        $this->shortClassName($operationClass)
                    );
                }

                continue;
            }

            $definitionSet[$key] = array_replace($definition, $definitionSet[$key]);
        }

        return $definitionSet;
    }

    private function shortClassName(string $class): string
    {
        $position = strrpos($class, '\\');

        return $position === false ? $class : substr($class, $position + 1);
    }

    private function generateDto(string $dtoClass, array $payload): ?CampaignCanonicalDTO
    {
        if (! is_subclass_of($dtoClass, CampaignCanonicalDTO::class) && $dtoClass !== CampaignCanonicalDTO::class) {
            $this->warn(sprintf('DTO class [%s] does not extend %s; attempting to instantiate regardless.', $dtoClass, CampaignCanonicalDTO::class));
        }

        $factory = new CanonicalFactory;

        try {
            $dto = $factory->make($dtoClass, [new ArraySource($payload)], $payload);
        } catch (\Throwable $exception) {
            $this->warn(sprintf(
                'CanonicalFactory failed to build DTO: %s. Falling back to raw fromArray.',
                $exception->getMessage()
            ));

            $dto = $dtoClass::fromArray($payload);
        }

        if (! $dto instanceof CampaignCanonicalDTO) {
            $this->warn(sprintf('DTO class [%s] did not produce a %s instance.', $dtoClass, CampaignCanonicalDTO::class));

            return null;
        }

        return $dto;
    }

    private function outputCanonicalSummary(CampaignCanonicalDTO $dto): void
    {
        $this->info('Canonical DTO array:');
        $this->line($this->formatPhpArray($dto->toArray()));
        $this->newLine();
        $this->info('Canonical DTO JSON:');
        $this->line(json_encode($dto->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function formatPhpArray(array $payload): string
    {
        $export = var_export($payload, true);

        return "\$payload = {$export};";
    }
}
