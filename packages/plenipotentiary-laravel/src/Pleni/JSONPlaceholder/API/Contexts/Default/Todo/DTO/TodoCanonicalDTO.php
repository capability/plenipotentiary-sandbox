<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\DTO;

use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;

final class TodoCanonicalDTO implements CanonicalDTOContract
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $title = null,
        public readonly ?bool $completed = null,
        public readonly ?int $userId = null,
        public readonly array $providerContext = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? $data['externalId'] ?? null,
            title: $data['title'] ?? null,
            completed: $data['completed'] ?? null,
            userId: $data['userId'] ?? null,
            providerContext: $data['providerContext'] ?? [],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'title' => $this->title,
            'completed' => $this->completed,
            'userId' => $this->userId,
            'providerContext' => $this->providerContext,
        ], fn ($value) => $value !== null);
    }

    public function getProviderContextValue(string $key): mixed
    {
        $keys = explode('.', $key);
        $value = $this->providerContext;

        foreach ($keys as $k) {
            if (! is_array($value) || ! array_key_exists($k, $value)) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }
}
