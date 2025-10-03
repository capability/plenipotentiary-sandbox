<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Transfer\Procedure;

use Plenipotentiary\Laravel\Contracts\Adapter\ProcedureAdapterContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsErrorMapper;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Saloon\Enums\Method;
use Saloon\Http\Response;
use Throwable;

/**
 * Google Ads Procedure/RPC adapter using Saloon.
 *
 * This adapter provides a simple RPC-style interface for calling Google Ads
 * endpoints without creating dedicated request classes. Operations are matched
 * internally and executed via Saloon HTTP requests.
 */
final class GoogleAdsProcedureAdapter implements ProcedureAdapterContract
{
    public function __construct(
        private GoogleAdsProcedureConnector $connector,
        private GoogleAdsErrorMapper $errorMapper,
        private LoggerInterface $logger,
    ) {}

    /**
     * Execute an RPC-style operation against Google Ads API.
     */
    public function call(string $operation, array $payload = [], array $options = []): Result
    {
        try {
            $this->logger->info('GoogleAds Procedure: Executing operation', [
                'operation' => $operation,
                'payload_keys' => array_keys($payload),
            ]);

            // Create a dynamic Saloon request based on the operation
            $request = $this->buildRequest($operation, $payload, $options);

            /** @var Response $response */
            $response = $this->connector->send($request);

            if ($response->successful()) {
                return Result::ok($response->json());
            }

            // Map HTTP error to domain error
            return $this->mapHttpError($response);
        } catch (Throwable $exception) {
            $this->logger->error('GoogleAds Procedure: Operation failed', [
                'operation' => $operation,
                'exception' => $exception->getMessage(),
            ]);

            $mapped = $this->errorMapper->map($exception);

            return Result::err([
                'code' => $mapped->code(),
                'message' => $mapped->getMessage(),
                'httpStatus' => $mapped->httpStatus(),
                'retryable' => $mapped->isRetryable(),
            ]);
        }
    }

    /**
     * Validate an operation without executing it.
     */
    public function validate(string $operation, array $payload = []): Result
    {
        // Add validateOnly flag for Google Ads
        $options = ['validateOnly' => true];

        return $this->call($operation, $payload, $options);
    }

    /**
     * Build a dynamic Saloon request based on the operation.
     */
    private function buildRequest(string $operation, array $payload, array $options): GoogleAdsDynamicRequest
    {
        // Map operation names to endpoint details
        $endpoint = $this->mapOperationToEndpoint($operation, $payload);

        return new GoogleAdsDynamicRequest(
            method: $endpoint['method'],
            endpoint: $endpoint['path'],
            body: $payload,
            query: $options['query'] ?? [],
            headers: $options['headers'] ?? [],
        );
    }

    /**
     * Map operation names to HTTP method and endpoint path.
     */
    private function mapOperationToEndpoint(string $operation, array $payload): array
    {
        return match ($operation) {
            'searchCampaigns', 'search' => [
                'method' => Method::POST,
                'path' => "/customers/{$payload['customerId']}/googleAds:search",
            ],
            'mutateCampaigns' => [
                'method' => Method::POST,
                'path' => "/customers/{$payload['customerId']}/campaigns:mutate",
            ],
            default => throw new \InvalidArgumentException("Unsupported operation: {$operation}"),
        };
    }

    private function mapHttpError(Response $response): Result
    {
        $body = $response->json();

        return Result::err([
            'code' => 'HTTP_ERROR',
            'message' => $body['error']['message'] ?? 'Unknown error',
            'httpStatus' => $response->status(),
            'retryable' => $response->status() >= 500,
            'details' => $body,
        ]);
    }
}
