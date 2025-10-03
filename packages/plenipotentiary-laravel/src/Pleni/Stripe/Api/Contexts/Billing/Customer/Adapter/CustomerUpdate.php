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
 * Update operation for Stripe Customers.
 *
 * Note: Stripe uses POST for updates, not PUT/PATCH!
 */
final class CustomerUpdate implements AdapterVerbContract
{
    public const INPUT_SPEC = [
        'externalId' => [
            'rules' => ['required', 'string'],
        ],
        'email' => [
            'rules' => ['nullable', 'email'],
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
            throw new \InvalidArgumentException('CustomerUpdate expects CustomerCanonicalDTO');
        }

        $this->logger->info('Updating Stripe customer via REST', [
            'externalId' => $dto->externalId,
        ]);

        if ($validateOnly) {
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
            throw new \InvalidArgumentException('CustomerUpdate expects CustomerCanonicalDTO');
        }

        return new class($dto) extends Request implements HasBody
        {
            use HasFormBody;

            protected Method $method = Method::POST; // Stripe uses POST for updates!

            public function __construct(private CustomerCanonicalDTO $dto) {}

            public function resolveEndpoint(): string
            {
                return '/v1/customers/'.$this->dto->externalId;
            }

            protected function defaultBody(): array
            {
                // Only include fields that are being updated
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
            throw new \InvalidArgumentException('CustomerUpdate::responseMapper expects (array, CustomerCanonicalDTO)');
        }

        return CustomerCanonicalDTO::fromArray([
            'externalId' => $response['id'],
            'email' => $response['email'] ?? $source->email,
            'name' => $response['name'] ?? $source->name,
            'description' => $response['description'] ?? $source->description,
            'metadata' => $response['metadata'] ?? $source->metadata,
            'providerContext' => $source->providerContext,
        ]);
    }
}
