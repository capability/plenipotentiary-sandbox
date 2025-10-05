<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Contexts\Default\Todo\DTO\TodoCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Shared\Transfer\Rest\JSONPlaceholderAPIRestConnector;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class TodoRead implements AdapterVerbContract
{
    public const INPUT_SPEC = [
        'id' => [
            'rules' => ['required', 'integer', 'min:1'],
        ],
    ];

    public function __construct(
        private ProviderClientContract $client,
        private LoggerInterface $logger,
    ) {}

    public static function inputSpec(): array
    {
        return self::INPUT_SPEC;
    }

    public function performWithArray(array $input, bool $validateOnly = false): Result
    {
        $dto = TodoCanonicalDTO::fromArray($input);

        return $this->perform($dto, $validateOnly);
    }

    public function perform(CanonicalDTOContract $dto, bool $validateOnly = false): Result
    {
        if (! $dto instanceof TodoCanonicalDTO) {
            throw new \InvalidArgumentException('TodoRead::perform expects TodoCanonicalDTO');
        }

        $request = $this->requestMapper($dto, $validateOnly);

        $this->logger->info('Reading JSONPlaceholder todo', [
            'id' => $dto->id,
        ]);

        /** @var JSONPlaceholderAPIRestConnector $connector */
        $connector = $this->client->raw();
        $response = $connector->send($request);

        $canonicalDto = $this->responseMapper($response, $dto);

        return Result::ok($canonicalDto, $response);
    }

    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly = false): mixed
    {
        if (! $dto instanceof TodoCanonicalDTO) {
            throw new \InvalidArgumentException('TodoRead::requestMapper expects TodoCanonicalDTO');
        }

        return new class($dto->id) extends Request
        {
            protected Method $method = Method::GET;

            public function __construct(
                private readonly int $id,
            ) {}

            public function resolveEndpoint(): string
            {
                return "/todos/{$this->id}";
            }
        };
    }

    public function responseMapper(mixed $response, mixed $source): CanonicalDTOContract
    {
        if (! $response instanceof Response || ! $source instanceof TodoCanonicalDTO) {
            throw new \InvalidArgumentException('TodoRead::responseMapper expects (Response, TodoCanonicalDTO)');
        }

        $data = $response->json();

        return TodoCanonicalDTO::fromArray([
            'id' => $data['id'] ?? null,
            'title' => $data['title'] ?? null,
            'completed' => $data['completed'] ?? null,
            'userId' => $data['userId'] ?? null,
            'providerContext' => $source->providerContext,
        ]);
    }
}
