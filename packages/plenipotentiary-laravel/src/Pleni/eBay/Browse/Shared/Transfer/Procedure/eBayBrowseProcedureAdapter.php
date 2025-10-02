<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Transfer\Procedure;

use Plenipotentiary\Laravel\Contracts\Adapter\ProcedureAdapterContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support\eBayErrorMapper;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Saloon\Enums\Method;
use Saloon\Http\Response;
use Throwable;

/**
 * eBay Browse Procedure/RPC adapter using Saloon.
 * 
 * This adapter provides a simple RPC-style interface for calling eBay Browse
 * endpoints without creating dedicated request classes. Operations are matched
 * internally and executed via Saloon HTTP requests.
 */
final class eBayBrowseProcedureAdapter implements ProcedureAdapterContract
{
    public function __construct(
        private eBayBrowseProcedureConnector $connector,
        private eBayErrorMapper $errorMapper,
        private LoggerInterface $logger,
    ) {}

    /**
     * Execute an RPC-style operation against eBay Browse API.
     */
    public function call(string $operation, array $payload = [], array $options = []): Result
    {
        try {
            $this->logger->info('eBay Browse Procedure: Executing operation', [
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
            $this->logger->error('eBay Browse Procedure: Operation failed', [
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
        // eBay doesn't have a validate-only mode, just return success
        return Result::ok(['validated' => true]);
    }

    /**
     * Build a dynamic Saloon request based on the operation.
     */
    private function buildRequest(string $operation, array $payload, array $options): eBayBrowseDynamicRequest
    {
        // Map operation names to endpoint details
        $endpoint = $this->mapOperationToEndpoint($operation, $payload);
        
        return new eBayBrowseDynamicRequest(
            method: $endpoint['method'],
            endpoint: $endpoint['path'],
            body: $endpoint['method'] === Method::GET ? [] : $payload,
            query: $endpoint['method'] === Method::GET ? $payload : ($options['query'] ?? []),
            headers: $options['headers'] ?? [],
        );
    }

    /**
     * Map operation names to HTTP method and endpoint path.
     */
    private function mapOperationToEndpoint(string $operation, array $payload): array
    {
        return match ($operation) {
            'searchItems', 'search' => [
                'method' => Method::GET,
                'path' => '/buy/browse/v1/item_summary/search',
            ],
            'getItem' => [
                'method' => Method::GET,
                'path' => '/buy/browse/v1/item/' . ($payload['itemId'] ?? ''),
            ],
            'getItemByLegacyId' => [
                'method' => Method::GET,
                'path' => '/buy/browse/v1/item/get_item_by_legacy_id',
            ],
            default => throw new \InvalidArgumentException("Unsupported operation: {$operation}"),
        };
    }

    private function mapHttpError(Response $response): Result
    {
        $body = $response->json();
        
        return Result::err([
            'code' => 'HTTP_ERROR',
            'message' => $body['errors'][0]['message'] ?? 'Unknown error',
            'httpStatus' => $response->status(),
            'retryable' => $response->status() >= 500,
            'details' => $body,
        ]);
    }
}
