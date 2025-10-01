<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Support;

/**
 * Helper to validate INPUT_SPEC arrays without pulling heavy validation deps.
 */
final class InputSpecValidator
{
    /**
     * @param  array<string,array<string,mixed>>  $spec
     * @param  array<string,mixed>  $values
     * @return array<int,array<string,mixed>>
     */
    public static function validate(array $spec, array $values): array
    {
        $violations = [];
        $expected = self::expectedStructure($spec);

        foreach ($spec as $field => $definition) {
            $rules = $definition['rules'] ?? [];
            $mapsTo = $definition['mapsTo'] ?? null;

            $valuePresent = array_key_exists($field, $values);
            $value = $values[$field] ?? null;

            foreach ($rules as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                if ($rule === 'required' && ! self::isFilled($valuePresent, $value)) {
                    $violations[] = self::violation((string) $field, $rule, $mapsTo);

                    continue;
                }

                if ($value === null || $value === '') {
                    continue; // no further rules apply when value absent and not required
                }

                if ($rule === 'string' && ! is_string($value)) {
                    $violations[] = self::violation((string) $field, $rule, $mapsTo);

                    continue;
                }

                if ($rule === 'numeric' && ! is_numeric($value)) {
                    $violations[] = self::violation((string) $field, $rule, $mapsTo);

                    continue;
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (is_string($value) && mb_strlen($value) < $min) {
                        $violations[] = self::violation((string) $field, $rule, $mapsTo);

                        continue;
                    }
                    if (is_numeric($value) && (float) $value < $min) {
                        $violations[] = self::violation((string) $field, $rule, $mapsTo);

                        continue;
                    }
                }

                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if (is_string($value) && mb_strlen($value) > $max) {
                        $violations[] = self::violation((string) $field, $rule, $mapsTo);

                        continue;
                    }
                    if (is_numeric($value) && (float) $value > $max) {
                        $violations[] = self::violation((string) $field, $rule, $mapsTo);

                        continue;
                    }
                }

                if (str_starts_with($rule, 'in:') && ! self::inRulePasses($rule, (string) $value)) {
                    $violations[] = self::violation((string) $field, $rule, $mapsTo);

                    continue;
                }
            }
        }

        return [
            'expected' => $expected,
            'violations' => $violations,
        ];
    }

    private static function expectedStructure(array $spec): array
    {
        $fields = [];
        $providerContext = [];

        foreach ($spec as $key => $definition) {
            $descriptor = [
                'required' => in_array('required', $definition['rules'] ?? [], true),
                'rules' => $definition['rules'] ?? [],
            ];

            if (isset($definition['default'])) {
                $descriptor['default'] = $definition['default'];
            }

            if (isset($definition['cast'])) {
                $descriptor['cast'] = $definition['cast'];
            }

            if (isset($definition['source'])) {
                $descriptor['source'] = $definition['source'];
            }

            $type = self::inferRuleType($definition['rules'] ?? []);
            if ($type !== null) {
                $descriptor['type'] = $type;
            }

            if (str_starts_with($key, 'providerContext.')) {
                $contextKey = substr($key, strlen('providerContext.'));
                $providerContext[$contextKey] = $descriptor;
            } else {
                $fields[$key] = $descriptor;
            }
        }

        return [
            'dto' => [
                'fields' => $fields,
                'providerContext' => $providerContext,
            ],
        ];
    }

    private static function inferRuleType(array $rules): ?string
    {
        foreach ($rules as $rule) {
            if ($rule === 'string') {
                return 'string';
            }
            if ($rule === 'numeric') {
                return 'numeric';
            }
            if (is_string($rule) && str_starts_with($rule, 'in:')) {
                return 'enum';
            }
        }
        return null;
    }

    private static function isFilled(bool $valuePresent, mixed $value): bool
    {
        if (! $valuePresent) {
            return false;
        }

        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        return true;
    }

    private static function inRulePasses(string $rule, string $value): bool
    {
        $allowed = explode(',', substr($rule, 3));

        return in_array($value, $allowed, true);
    }

    private static function violation(string $field, string $rule, ?string $mapsTo = null): array
    {
        $message = self::messageFor($rule, $field);
        
        return array_filter([
            'field' => $field,
            'rule' => $rule,
            'mapsTo' => $mapsTo,
            'message' => $message,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private static function messageFor(string $rule, string $field): string
    {
        if ($rule === 'required') {
            return "Required";
        }
        if ($rule === 'string') {
            return "Must be a string";
        }
        if ($rule === 'numeric') {
            return "Must be numeric";
        }
        if (str_starts_with($rule, 'min:')) {
            $min = substr($rule, 4);
            return "Must be at least {$min}";
        }
        if (str_starts_with($rule, 'max:')) {
            $max = substr($rule, 4);
            return "Must not exceed {$max}";
        }
        if (str_starts_with($rule, 'in:')) {
            $values = substr($rule, 3);
            return "Must be one of: {$values}";
        }
        
        return "Validation failed for rule: {$rule}";
    }
}
