<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Support;

use InvalidArgumentException;
use Plenipotentiary\Laravel\Support\InputSource\InputSource;
use RuntimeException;

final class CanonicalFactory
{
    /**
     * @param  class-string  $dtoClass
     * @param  array<int,InputSource>  $sources
     */
    public function make(string $dtoClass, array $sources, array $overrides = []): object
    {
        if (! method_exists($dtoClass, 'schema') || ! method_exists($dtoClass, 'fromArray')) {
            throw new InvalidArgumentException(sprintf('%s must expose schema() and fromArray().', $dtoClass));
        }

        /** @var array<string,array<string,mixed>> $schema */
        $schema = $dtoClass::schema();
        $payload = array_merge($this->collect($schema, $sources), $overrides);

        /** @var callable(array):object $fromArray */
        $fromArray = [$dtoClass, 'fromArray'];
        $dto = $fromArray($payload);

        if (! is_object($dto)) {
            throw new RuntimeException(sprintf('%s::fromArray must return a DTO instance.', $dtoClass));
        }

        return $dto;
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
