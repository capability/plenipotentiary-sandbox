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

final class TodoCreate implements AdapterVerbContract
{
    public const INPUT_SPEC = [
        'title' => [
            'rules' => ['required', 'string', 'min:1', 'max:255'],
        ],
        'completed' => [
            'rules' => ['nullable', 'boolean'],
        ],
        'userId' => [
            'rules' => ['nullable', 'integer', 'min:1'],
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
            throw new \InvalidArgumentException('TodoCreate::perform expects TodoCanonicalDTO');
        }

        $request = $this->requestMapper($dto, $validateOnly);

        $this->logger->info('Creating JSONPlaceholder todo', [
            'title' => $dto->title,
            'validateOnly' => $validateOnly,
        ]);

        if ($validateOnly) {
            return Result::ok($dto);
        }

        /** @var JSONPlaceholderAPIRestConnector $connector */
        $connector = $this->client->raw();
        $response = $connector->send($request);

        $canonicalDto = $this->responseMapper($response, $dto);

        return Result::ok($canonicalDto, $response);
    }

    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly = false): mixed
    {
        if (! $dto instanceof TodoCanonicalDTO) {
            throw new \InvalidArgumentException('TodoCreate::requestMapper expects TodoCanonicalDTO');
        }

        return new class($dto->title, $dto->completed ?? false, $dto->userId) extends Request
        {
            protected Method $method = Method::POST;

            public function __construct(
                private readonly string $title,
                private readonly bool $completed,
                private readonly ?int $userId,
            ) {}

            public function resolveEndpoint(): string
            {
                return '/todos';
            }

            protected function defaultBody(): array
            {
                return array_filter([
                    'title' => $this->title,
                    'completed' => $this->completed,
                    'userId' => $this->userId,
                ], fn ($value) => $value !== null);
            }
        };
    }

    public function responseMapper(mixed $response, mixed $source): CanonicalDTOContract
    {
        if (! $response instanceof Response || ! $source instanceof TodoCanonicalDTO) {
            throw new \InvalidArgumentException('TodoCreate::responseMapper expects (Response, TodoCanonicalDTO)');
        }

        $data = $response->json();

        return TodoCanonicalDTO::fromArray([
            'id' => $data['id'] ?? null,
            'title' => $data['title'] ?? $source->title,
            'completed' => $data['completed'] ?? $source->completed,
            'userId' => $data['userId'] ?? $source->userId,
            'providerContext' => $source->providerContext,
        ]);
    }
}
