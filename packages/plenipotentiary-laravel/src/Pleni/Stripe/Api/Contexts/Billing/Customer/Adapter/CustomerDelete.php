<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Contracts\DTO\CanonicalDTOContract;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Contexts\Billing\Customer\DTO\CustomerCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Stripe\Api\Shared\Transfer\Rest\StripeApiRestConnector;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Delete operation for Stripe Customers.
 */
final class CustomerDelete implements AdapterVerbContract
{
    public const INPUT_SPEC = [
        'externalId' => [
            'rules' => ['required', 'string'],
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
            throw new \InvalidArgumentException('CustomerDelete expects CustomerCanonicalDTO');
        }

        $this->logger->info('Deleting Stripe customer via REST', [
            'externalId' => $dto->externalId,
        ]);

        if ($validateOnly) {
            return Result::ok();
        }

        $request = $this->requestMapper($dto);
        $response = $this->connector->send($request);

        if (! $response->successful()) {
            return Result::err([
                'code' => 'STRIPE_ERROR',
                'message' => $response->json()['error']['message'] ?? 'Delete failed',
                'httpStatus' => $response->status(),
            ]);
        }

        return Result::ok($this->responseMapper($response->json(), $dto));
    }

    public function requestMapper(CanonicalDTOContract $dto, bool $validateOnly = false): Request
    {
        if (! $dto instanceof CustomerCanonicalDTO) {
            throw new \InvalidArgumentException('CustomerDelete expects CustomerCanonicalDTO');
        }

        return new class($dto) extends Request
        {
            protected Method $method = Method::DELETE;

            public function __construct(private CustomerCanonicalDTO $dto) {}

            public function resolveEndpoint(): string
            {
                return '/v1/customers/' . $this->dto->externalId;
            }
        };
    }

    public function responseMapper(mixed $response, mixed $source): CanonicalDTOContract
    {
        if (! is_array($response) || ! $source instanceof CustomerCanonicalDTO) {
            throw new \InvalidArgumentException('CustomerDelete::responseMapper expects (array, CustomerCanonicalDTO)');
        }

        // Stripe returns {id, object: "customer", deleted: true}
        return CustomerCanonicalDTO::fromArray([
            'externalId' => $response['id'],
            'providerContext' => [
                'stripe' => [
                    'deleted' => $response['deleted'] ?? false,
                ],
            ],
        ]);
    }
}
