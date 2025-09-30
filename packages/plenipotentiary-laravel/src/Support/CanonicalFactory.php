<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Support;

use Plenipotentiary\Laravel\Support\InputSource\InputSource;
use Plenipotentiary\Laravel\Support\Result;

final class CanonicalFactory
{
    /**
     * @param  class-string  $dtoClass
     * @param  array<int,InputSource>  $sources
     */
    public function make(string $dtoClass, array $sources, array $overrides = []): Result
    {
        if (! method_exists($dtoClass, 'schema') || ! method_exists($dtoClass, 'fromArray')) {
            return Result::err([
                'error' => 'invalid_dto_contract',
                'message' => sprintf('%s must expose schema() and fromArray()', $dtoClass),
            ]);
        }

        /** @var array<string,array<string,mixed>> $schema */
        $schema = $dtoClass::schema();
        $payload = array_merge($this->collect($schema, $sources), $overrides);

        $violations = InputSpecValidator::validate($schema, $payload);
        if ($violations) {
            return Result::invalid($violations);
        }

        /** @var callable(array):object $fromArray */
        $fromArray = [$dtoClass, 'fromArray'];

        return Result::ok($fromArray($payload));
    }

    /**
     * @param  array<string,array<string,mixed>>  $schema
     * @param  array<int,InputSource>  $sources
     * @return array<string,mixed>
     */
    private function collect(array $schema, array $sources): array
    {
        $payload = [];

        foreach ($schema as $field => $definition) {
            $key = $definition['key'] ?? $field;
            $value = null;
            $found = false;

            foreach ($sources as $source) {
                if (! $source instanceof InputSource) {
                    continue;
                }

                $candidate = $source->get($key);
                if ($candidate !== null) {
                    $value = $candidate;
                    $found = true;
                    break;
                }
            }

            if (! $found && array_key_exists('default', $definition)) {
                $value = $definition['default'];
            }

            if ($value !== null && array_key_exists('cast', $definition)) {
                $value = $this->castValue($definition['cast'], $value);
            }

            $payload[$field] = $value;
        }

        return $payload;
    }

    private function castValue(mixed $cast, mixed $value): mixed
    {
        if (is_callable($cast)) {
            return $cast($value);
        }

        if (! is_string($cast)) {
            return $value;
        }

        return match ($cast) {
            'currency_to_micros' => (int) round(((float) $value) * 1_000_000),
            'int' => (int) $value,
            'float' => (float) $value,
            'string' => (string) $value,
            default => $value,
        };
    }
}
