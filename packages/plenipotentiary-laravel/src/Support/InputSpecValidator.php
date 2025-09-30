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

        return $violations;
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
        return array_filter([
            'field' => $field,
            'rule' => $rule,
            'mapsTo' => $mapsTo,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
