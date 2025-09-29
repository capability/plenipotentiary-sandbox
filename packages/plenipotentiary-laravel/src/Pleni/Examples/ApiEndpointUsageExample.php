<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Examples;

use Plenipotentiary\Laravel\Contracts\Gateway\ApiEndpointGatewayContract;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Example usage of the ApiEndpointGateway pattern
 *
 * This demonstrates how to use the flexible gateway/adapter pattern
 * for non-CRUD APIs like eBay Browse and OpenAI.
 *
 * Structure: Pleni/Provider/Domain/Contexts/Default/Endpoint/
 */
class ApiEndpointUsageExample
{
    public function __construct(
        private ApiEndpointGatewayContract $gateway,
    ) {}

    /**
     * Example: eBay Browse API usage
     */
    public function searchEBayItems(): Result
    {
        // Search for items
        return $this->gateway->call('searchItems', [
            'q' => 'vintage guitar',
            'limit' => 25,
            'category_ids' => [619, 33033], // Musical Instruments categories
            'sort' => 'price',
        ]);
    }

    /**
     * Example: Get specific eBay item
     */
    public function getEBayItem(string $itemId): Result
    {
        return $this->gateway->call('getItem', [], [
            'itemId' => $itemId,
        ]);
    }

    /**
     * Example: Create eBay offer
     */
    public function createEBayOffer(): Result
    {
        return $this->gateway->call('createOffer', [
            'listingDuration' => 'GTC', // Good 'Til Cancelled
            'quantity' => 1,
            'pricingSummary' => [
                'price' => ['value' => '99.99', 'currency' => 'USD'],
            ],
            'merchantLocationKey' => 'default',
            'format' => 'FIXED_PRICE',
            'marketplaceId' => 'EBAY_US',
        ]);
    }

    /**
     * Example: OpenAI API usage
     */
    public function createOpenAICompletion(): Result
    {
        return $this->gateway->call('createCompletion', [
            'model' => 'text-davinci-003',
            'prompt' => 'Write a haiku about coding:',
            'max_tokens' => 50,
            'temperature' => 0.7,
        ]);
    }

    /**
     * Example: Create chat completion
     */
    public function createChatCompletion(): Result
    {
        return $this->gateway->call('createChatCompletion', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello! How are you?'],
            ],
            'max_tokens' => 100,
            'temperature' => 0.7,
        ]);
    }

    /**
     * Example: Create image
     */
    public function createImage(): Result
    {
        return $this->gateway->call('createImage', [
            'prompt' => 'A serene mountain landscape at sunset',
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
        ]);
    }

    /**
     * Example: List OpenAI models
     */
    public function listModels(): Result
    {
        return $this->gateway->call('listModels');
    }

    /**
     * Example: Validate operation before executing
     */
    public function validateBeforeCreate(): Result
    {
        // Validate the completion request
        $validationResult = $this->gateway->validate('createCompletion', [
            'model' => 'text-davinci-003',
            'prompt' => 'Test prompt',
        ]);

        if ($validationResult->isInvalid()) {
            return $validationResult;
        }

        // If validation passes, create the completion
        return $this->gateway->call('createCompletion', [
            'model' => 'text-davinci-003',
            'prompt' => 'Write a short story about a robot:',
            'max_tokens' => 200,
        ]);
    }
}
