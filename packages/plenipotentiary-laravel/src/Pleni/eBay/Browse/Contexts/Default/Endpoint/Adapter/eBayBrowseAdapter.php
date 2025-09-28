<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Endpoint\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\ApiEndpointAdapterContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Support\Result;
use Psr\Log\LoggerInterface;

/**
 * eBay Browse API Adapter
 * 
 * Provider-specific implementation for eBay Browse API operations.
 * Handles API communication, request building, and response mapping.
 */
final class eBayBrowseAdapter implements ApiEndpointAdapterContract
{
    public function __construct(
        private ProviderClientContract $client,
        private ErrorMapperContract $errorMapper,
        private LoggerInterface $logger,
    ) {}

    public function call(string $operation, array $payload = [], array $options = []): Result
    {
        try {
            $endpointConfig = $this->getEndpointConfig($operation);
            
            $request = $this->buildRequest($endpointConfig, $payload, $options);
            
            $this->logger->info('eBay API call', [
                'operation' => $operation,
                'method' => $endpointConfig['method'],
                'endpoint' => $endpointConfig['endpoint'],
            ]);

            $response = $this->client->request(
                $endpointConfig['method'],
                $endpointConfig['endpoint'],
                $request
            );

            return Result::ok($response->json());

        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    public function validate(string $operation, array $payload = []): Result
    {
        try {
            $endpointConfig = $this->getEndpointConfig($operation);
            
            // eBay-specific validation logic
            $violations = $this->validatePayload($operation, $payload);
            if ($violations) {
                return Result::invalid($violations);
            }

            // Make validate-only call to eBay (if supported)
            $request = $this->buildRequest($endpointConfig, $payload, ['validate_only' => true]);
            $response = $this->client->request(
                $endpointConfig['method'],
                $endpointConfig['endpoint'],
                $request
            );

            return Result::ok($response->json());

        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    /**
     * Get endpoint configuration for an operation
     */
    private function getEndpointConfig(string $operation): array
    {
        return match ($operation) {
            'searchItems' => [
                'method' => 'GET',
                'endpoint' => '/buy/browse/v1/item_summary/search'
            ],
            'getItem' => [
                'method' => 'GET', 
                'endpoint' => '/buy/browse/v1/item/{itemId}'
            ],
            'getItemByLegacyId' => [
                'method' => 'GET',
                'endpoint' => '/buy/browse/v1/item/get_item_by_legacy_id'
            ],
            'searchByImage' => [
                'method' => 'POST',
                'endpoint' => '/buy/browse/v1/item_summary/search_by_image'
            ],
            'getItemAspectsForCategory' => [
                'method' => 'GET',
                'endpoint' => '/buy/browse/v1/item_aspect_search_by_category'
            ],
            'createOffer' => [
                'method' => 'POST',
                'endpoint' => '/sell/offer/v1_beta/offer'
            ],
            'updateOffer' => [
                'method' => 'PUT',
                'endpoint' => '/sell/offer/v1_beta/offer/{offerId}'
            ],
            'deleteOffer' => [
                'method' => 'DELETE',
                'endpoint' => '/sell/offer/v1_beta/offer/{offerId}'
            ],
            'getOffer' => [
                'method' => 'GET',
                'endpoint' => '/sell/offer/v1_beta/offer/{offerId}'
            ],
            default => throw new \InvalidArgumentException("Unknown operation: {$operation}")
        };
    }

    /**
     * Validate payload for specific operations
     */
    private function validatePayload(string $operation, array $payload): array
    {
        $violations = [];

        switch ($operation) {
            case 'searchItems':
                if (empty($payload['q']) && empty($payload['category_ids'])) {
                    $violations[] = [
                        'field' => 'q_or_category_ids',
                        'rule' => 'required',
                        'message' => 'Either search query (q) or category_ids is required'
                    ];
                }
                break;
                
            case 'getItem':
                // Item ID should be provided in options, not payload
                break;
                
            case 'createOffer':
                if (empty($payload['listingDuration'])) {
                    $violations[] = [
                        'field' => 'listingDuration',
                        'rule' => 'required',
                        'message' => 'Listing duration is required'
                    ];
                }
                if (empty($payload['quantity'])) {
                    $violations[] = [
                        'field' => 'quantity',
                        'rule' => 'required',
                        'message' => 'Quantity is required'
                    ];
                }
                break;
                
            case 'updateOffer':
            case 'deleteOffer':
            case 'getOffer':
                // Offer ID should be provided in options
                break;
        }

        return $violations;
    }

    /**
     * Build HTTP request from endpoint config, payload, and options
     */
    private function buildRequest(array $config, array $payload, array $options): array
    {
        $request = [
            'headers' => $options['headers'] ?? [],
        ];

        // Handle URL parameters
        $endpoint = $config['endpoint'];
        if (isset($options['itemId'])) {
            $endpoint = str_replace('{itemId}', $options['itemId'], $endpoint);
        }
        if (isset($options['offerId'])) {
            $endpoint = str_replace('{offerId}', $options['offerId'], $endpoint);
        }

        // Set endpoint with parameters
        $request['endpoint'] = $endpoint;

        // Handle request body based on method
        if (in_array($config['method'], ['POST', 'PUT', 'PATCH'], true)) {
            $request['json'] = $payload;
        } else {
            // For GET requests, add payload as query parameters
            $request['query'] = $payload;
        }

        // Add validate-only flag if specified
        if (isset($options['validate_only']) && $options['validate_only']) {
            $request['headers']['X-eBay-C-Validate-Only'] = 'true';
        }

        return $request;
    }
}
