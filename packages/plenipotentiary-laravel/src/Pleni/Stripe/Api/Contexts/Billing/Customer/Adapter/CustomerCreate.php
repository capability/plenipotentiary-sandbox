<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\DTO\CustomerCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Transfer\Rest\StripeApiRestConnector;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasFormBody;

/**
 * Create operation for Stripe Customers using Saloon.
 * 
 * This demonstrates how AdapterVerbContract can be implemented
 * using REST API calls via Saloon.
 */
final class CustomerCreate implements AdapterVerbContract
{
    public const INPUT_SPEC = [
        'email' => [
            'rules' => ['required', 'email'],
        ],
        'name' => [
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'description' => [
            'rules' => ['nullable', 'string', 'max:500'],
        ],
    ];

    public function __construct(
        private StripeApiRestConnector $connector,
        private LoggerInterface $logger,
    ) {}

    public static function inputSpec(): array
    {
        return self::INPUT_SPEC;
    }

    public function perform(CanonicalDTOContract $dto, bool $validateOnly = false): Result
    {
        if (! $dto instanceof CustomerCanonicalDTO) {
            throw new \InvalidArgumentException('CustomerCreate expects CustomerCanonicalDTO');
        }

        $this->logger->info('Creating Stripe customer via REST', [
            'email' => $dto->email,
            'validateOnly' => $validateOnly,
        ]);

        if ($validateOnly) {
            // For REST APIs without native validation, we just skip the call
            return Result::ok($dto);
        }

        $request = $this->requestMapper($dto);
        $response = $this->connector->send($request);

        if (! $response->successful()) {
            return Result::err([
                'code' => 'STRIPE_ERROR',
                'message' => $response->json()['error']['message'] ?? 'Unknown error',
                'httpStatus' => $response->status(),
            ]);
        }

        return Result::ok($this->responseMapper($response->json(), $dto));
    }

    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly = false): Request
    {
        if (! $dto instanceof CustomerCanonicalDTO) {
            throw new \InvalidArgumentException('CustomerCreate expects CustomerCanonicalDTO');
        }

        return new class($dto) extends Request implements HasBody
        {
            use HasFormBody; // Stripe uses form-encoded bodies

            protected Method $method = Method::POST;

            public function __construct(private CustomerCanonicalDTO $dto) {}

            public function resolveEndpoint(): string
            {
                return '/v1/customers';
            }

            protected function defaultBody(): array
            {
                return array_filter([
                    'email' => $this->dto->email,
                    'name' => $this->dto->name,
                    'description' => $this->dto->description,
                    'metadata' => $this->dto->metadata,
                ], fn ($value) => $value !== null);
            }
        };
    }

    public function responseMapper(mixed $response, mixed $source): CanonicalDTOContract
    {
        if (! is_array($response) || ! $source instanceof CustomerCanonicalDTO) {
            throw new \InvalidArgumentException('CustomerCreate::responseMapper expects (array, CustomerCanonicalDTO)');
        }

        return CustomerCanonicalDTO::fromArray([
            'externalId' => $response['id'],
            'email' => $response['email'],
            'name' => $response['name'],
            'description' => $response['description'],
            'metadata' => $response['metadata'] ?? [],
            'providerContext' => [
                'stripe' => [
                    'created' => $response['created'],
                    'livemode' => $response['livemode'],
                ],
            ],
        ]);
    }
}
