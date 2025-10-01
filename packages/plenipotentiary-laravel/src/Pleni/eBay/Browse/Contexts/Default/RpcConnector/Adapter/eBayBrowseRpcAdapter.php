<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\RpcConnector\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\RpcAdapterContract;
use Plenipotentiary\Laravel\Contracts\Client\HttpProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class EbayBrowseRpcAdapter implements RpcAdapterContract
{
    public function __construct(
        private HttpProviderClientContract $client,
        private ErrorMapperContract $errorMapper,
        private LoggerInterface $logger,
    ) {}

    public function call(string $operation, array $payload = [], array $options = []): Result
    {
        try {
            $endpointConfig = $this->getEndpointConfig($operation);
            [$endpoint, $request] = $this->buildRequest($endpointConfig, $payload, $options);

            $this->logger->info('eBay API call', [
                'operation' => $operation,
                'method' => $endpointConfig['method'],
                'endpoint' => $endpoint,
            ]);

            $response = $this->client->request(
                $endpointConfig['method'],
                $endpoint,
                $request
            );

            $data = json_decode((string) $response->getBody(), true) ?? [];

            return Result::ok($data);

        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    public function validate(string $operation, array $payload = []): Result
    {
        try {
            $endpointConfig = $this->getEndpointConfig($operation);
            $violations = $this->validatePayload($operation, $payload);
            if ($violations) {
                return Result::invalid($violations);
            }

            // Optionally, hit provider validate-only where supported
            [$endpoint, $request] = $this->buildRequest($endpointConfig, $payload, ['validate_only' => true] + $options ?? []);
            $response = $this->client->request($endpointConfig['method'], $endpoint, $request);

            $data = json_decode((string) $response->getBody(), true) ?? [];

            return Result::ok($data);

        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    private function getEndpointConfig(string $operation): array
    {
        return match ($operation) {
            'searchItems' => ['method' => 'GET', 'endpoint' => '/buy/browse/v1/item_summary/search'],
            'getItem' => ['method' => 'GET', 'endpoint' => '/buy/browse/v1/item/{itemId}'],
            'getItemByLegacyId' => ['method' => 'GET', 'endpoint' => '/buy/browse/v1/item/get_item_by_legacy_id'],
            'searchByImage' => ['method' => 'POST', 'endpoint' => '/buy/browse/v1/item_summary/search_by_image'],
            'getItemAspectsForCategory' => ['method' => 'GET', 'endpoint' => '/buy/browse/v1/item_aspect_search_by_category'],
            'createOffer' => ['method' => 'POST', 'endpoint' => '/sell/offer/v1_beta/offer'],
            'updateOffer' => ['method' => 'PUT', 'endpoint' => '/sell/offer/v1_beta/offer/{offerId}'],
            'deleteOffer' => ['method' => 'DELETE', 'endpoint' => '/sell/offer/v1_beta/offer/{offerId}'],
            'getOffer' => ['method' => 'GET', 'endpoint' => '/sell/offer/v1_beta/offer/{offerId}'],
            default => throw new \InvalidArgumentException("Unknown operation: {$operation}")
        };
    }

    private function validatePayload(string $operation, array $payload): array
    {
        $violations = [];
        switch ($operation) {
            case 'searchItems':
                if (empty($payload['q']) && empty($payload['category_ids'])) {
                    $violations[] = ['field' => 'q_or_category_ids', 'rule' => 'required', 'message' => 'Either search query (q) or category_ids is required'];
                }
                break;
            case 'createOffer':
                if (empty($payload['listingDuration'])) {
                    $violations[] = ['field' => 'listingDuration', 'rule' => 'required', 'message' => 'Listing duration is required'];
                }
                if (empty($payload['quantity'])) {
                    $violations[] = ['field' => 'quantity', 'rule' => 'required', 'message' => 'Quantity is required'];
                }
                break;
        }

        return $violations;
    }

    /**
     * @return array{0:string,1:array}
     */
    private function buildRequest(array $config, array $payload, array $options): array
    {
        $request = [
            'headers' => $options['headers'] ?? [],
        ];

        $endpoint = $config['endpoint'];
        if (isset($options['itemId'])) {
            $endpoint = str_replace('{itemId}', $options['itemId'], $endpoint);
        }
        if (isset($options['offerId'])) {
            $endpoint = str_replace('{offerId}', $options['offerId'], $endpoint);
        }

        if (in_array($config['method'], ['POST', 'PUT', 'PATCH'], true)) {
            $request['json'] = $payload;
        } else {
            $request['query'] = $payload;
        }

        if (! empty($options['validate_only'])) {
            $request['headers']['X-eBay-C-Validate-Only'] = 'true';
        }

        return [$endpoint, $request];
    }
}
