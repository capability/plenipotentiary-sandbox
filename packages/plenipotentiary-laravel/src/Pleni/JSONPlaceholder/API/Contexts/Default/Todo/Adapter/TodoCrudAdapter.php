<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\CrudAdapterContract;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\DTO\TodoCanonicalDTO;
use Plenipotentiary\Laravel\Support\Result;

final class TodoCrudAdapter implements CrudAdapterContract
{
    public function __construct(
        private TodoCreate $createOperation,
        private TodoUpdate $updateOperation,
        private TodoDelete $deleteOperation,
        private TodoRead $readOperation,
        private TodoList $listOperation,
    ) {}

    public function create(TodoCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        return $this->createOperation->perform($dto, $validateOnly);
    }

    public function update(TodoCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        return $this->updateOperation->perform($dto, $validateOnly);
    }

    public function find(TodoCanonicalDTO $dto): Result
    {
        return $this->readOperation->perform($dto);
    }

    public function list(?int $userId = null): Result
    {
        $dto = TodoCanonicalDTO::fromArray(['userId' => $userId]);

        return $this->listOperation->perform($dto);
    }

    public function delete(TodoCanonicalDTO $dto, bool $validateOnly = false): Result
    {
        return $this->deleteOperation->perform($dto, $validateOnly);
    }
}
