<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Auth;

use Bricre\EbaySdkBuyBrowse\Configuration;
use Bricre\EbaySdkBuyBrowse\Api\ItemSummaryApi;
use Bricre\EbaySdkBuyBrowse\Api\ItemApi;
use Bricre\EbaySdkBuyBrowse\Api\OfferApi;
use GuzzleHttp\Client as GuzzleClient;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;

/**
 * eBay Browse SDK Client Wrapper
 *
 * Provides a unified interface to eBay Browse API services.
 * Wraps the official eBay Browse SDK with consistent error handling and logging.
 */
final class eBaySdkClient implements ProviderClientContract
{
    private Configuration $config;
    private GuzzleClient $httpClient;
    private ItemSummaryApi $itemSummaryApi;
    private ItemApi $itemApi;
    private OfferApi $offerApi;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->httpClient = new GuzzleClient([
            'base_uri' => 'https://api.ebay.com',
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->initializeApiClients();
    }

    /**
     * Initialize all eBay Browse API clients
     */
    private function initializeApiClients(): void
    {
        $this->itemSummaryApi = new ItemSummaryApi($this->httpClient, $this->config);
        $this->itemApi = new ItemApi($this->httpClient, $this->config);
        $this->offerApi = new OfferApi($this->httpClient, $this->config);
    }

    /**
     * Get the raw HTTP client for direct API calls
     */
    public function request(string $method, string $endpoint, array $options = []): \Psr\Http\Message\ResponseInterface
    {
        $requestOptions = $this->prepareRequestOptions($options);
        
        return $this->httpClient->request($method, $endpoint, $requestOptions);
    }

    /**
     * Get the eBay Browse ItemSummary API client
     */
    public function getItemSummaryApi(): ItemSummaryApi
    {
        return $this->itemSummaryApi;
    }

    /**
     * Get the eBay Browse Item API client
     */
    public function getItemApi(): ItemApi
    {
        return $this->itemApi;
    }

    /**
     * Get the eBay Browse Offer API client
     */
    public function getOfferApi(): OfferApi
    {
        return $this->offerApi;
    }

    /**
     * Get the raw eBay SDK Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->config;
    }

    /**
     * Get the raw HTTP client
     */
    public function getHttpClient(): GuzzleClient
    {
        return $this->httpClient;
    }

    /**
     * Prepare request options for HTTP client
     */
    private function prepareRequestOptions(array $options): array
    {
        $requestOptions = [];

        // Handle headers
        if (isset($options['headers'])) {
            $requestOptions['headers'] = $options['headers'];
        }

        // Handle JSON body
        if (isset($options['json'])) {
            $requestOptions['json'] = $options['json'];
        }

        // Handle query parameters
        if (isset($options['query'])) {
            $requestOptions['query'] = $options['query'];
        }

        // Handle form data
        if (isset($options['form_params'])) {
            $requestOptions['form_params'] = $options['form_params'];
        }

        // Handle multipart data
        if (isset($options['multipart'])) {
            $requestOptions['multipart'] = $options['multipart'];
        }

        // Handle timeout
        if (isset($options['timeout'])) {
            $requestOptions['timeout'] = $options['timeout'];
        }

        // Add eBay-specific headers
        $requestOptions['headers'] = array_merge(
            $requestOptions['headers'] ?? [],
            [
                'X-EBAY-C-MARKETPLACE-ID' => env('EBAY_MARKETPLACE_ID', 'EBAY_US'),
                'X-EBAY-C-ENDUSERCTX' => env('EBAY_ENDUSERCTX', 'affiliateCampaignId=<ePNCampaignId>,affiliateReferenceId=<referenceId>'),
            ]
        );

        return $requestOptions;
    }

    /**
     * Search for items using the Browse API
     */
    public function searchItems(array $params = []): array
    {
        try {
            $result = $this->itemSummaryApi->search(
                $params['q'] ?? null,
                $params['category_ids'] ?? null,
                $params['filter'] ?? null,
                $params['sort'] ?? null,
                $params['limit'] ?? null,
                $params['offset'] ?? null,
                $params['aspect_filter'] ?? null,
                $params['epid'] ?? null,
                $params['gtin'] ?? null,
                $params['charity_ids'] ?? null,
                $params['fieldgroups'] ?? null,
                $params['compatibility_filter'] ?? null,
                $params['auto_correct'] ?? null
            );

            return $result->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException("eBay search failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get item details by ID
     */
    public function getItem(string $itemId): array
    {
        try {
            $result = $this->itemApi->getItem($itemId);
            return $result->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException("eBay get item failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get item by legacy ID
     */
    public function getItemByLegacyId(string $legacyId, array $legacyVariants = []): array
    {
        try {
            $result = $this->itemApi->getItemByLegacyId($legacyId, $legacyVariants);
            return $result->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException("eBay get item by legacy ID failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Search by image
     */
    public function searchByImage(array $imageData): array
    {
        try {
            $result = $this->itemSummaryApi->searchByImage(
                $imageData['image'] ?? null,
                $imageData['category_ids'] ?? null,
                $imageData['filter'] ?? null,
                $imageData['sort'] ?? null,
                $imageData['limit'] ?? null,
                $imageData['offset'] ?? null,
                $imageData['aspect_filter'] ?? null,
                $imageData['epid'] ?? null,
                $imageData['gtin'] ?? null,
                $imageData['charity_ids'] ?? null,
                $imageData['fieldgroups'] ?? null,
                $imageData['compatibility_filter'] ?? null
            );

            return $result->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException("eBay search by image failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get item aspects for category
     */
    public function getItemAspectsForCategory(string $categoryId): array
    {
        try {
            $result = $this->itemApi->getItemAspectsForCategory($categoryId);
            return $result->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException("eBay get item aspects failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Create offer (requires Sell API access)
     */
    public function createOffer(array $offerData): array
    {
        try {
            $result = $this->offerApi->createOffer($offerData);
            return $result->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException("eBay create offer failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Update offer (requires Sell API access)
     */
    public function updateOffer(string $offerId, array $offerData): array
    {
        try {
            $result = $this->offerApi->updateOffer($offerId, $offerData);
            return $result->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException("eBay update offer failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Delete offer (requires Sell API access)
     */
    public function deleteOffer(string $offerId): array
    {
        try {
            $result = $this->offerApi->deleteOffer($offerId);
            return $result->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException("eBay delete offer failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get offer (requires Sell API access)
     */
    public function getOffer(string $offerId): array
    {
        try {
            $result = $this->offerApi->getOffer($offerId);
            return $result->toArray();
        } catch (\Exception $e) {
            throw new \RuntimeException("eBay get offer failed: " . $e->getMessage(), 0, $e);
        }
    }
}
