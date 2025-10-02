<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\GetItemAction;

/**
 * Job for syncing eBay item details in the background.
 * 
 * This demonstrates how the eBay Browse API can be used in Laravel's
 * queue system. Because the gateway handles idempotency and retry logic,
 * this job can safely be retried on failure.
 * 
 * Example usage:
 *   dispatch(new SyncItemDetailsJob('v1|272535166916|0', $productId));
 *   SyncItemDetailsJob::dispatch('v1|272535166916|0', 123)->onQueue('ebay-sync');
 */
class SyncItemDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    /**
     * @param string $ebayItemId The eBay item ID to sync
     * @param int|null $localProductId Optional local product ID for persistence
     * @param bool $checkCompact Whether to check compact first for changes
     */
    public function __construct(
        private readonly string $ebayItemId,
        private readonly ?int $localProductId = null,
        private readonly bool $checkCompact = true,
    ) {}

    public function handle(GetItemAction $getItemAction): void
    {
        Log::info('Syncing eBay item details', [
            'ebay_item_id' => $this->ebayItemId,
            'local_product_id' => $this->localProductId,
        ]);

        try {
            // Step 1: Check if item has changed (optional optimization)
            if ($this->checkCompact) {
                $compactResult = $getItemAction->getCompact($this->ebayItemId);
                
                if ($compactResult->isErr()) {
                    $this->handleError($compactResult->unwrapErr());
                    return;
                }

                // Here you would check sellerItemRevision against stored value
                // to determine if a full fetch is needed
            }

            // Step 2: Fetch full item details
            $result = $getItemAction->getWithProduct($this->ebayItemId);

            if ($result->isErr()) {
                $this->handleError($result->unwrapErr());
                return;
            }

            $itemData = $result->unwrap();

            // Step 3: Persist to local database
            $this->persistItemData($itemData);

            Log::info('Successfully synced eBay item', [
                'ebay_item_id' => $this->ebayItemId,
                'title' => $itemData['title'] ?? 'Unknown',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to sync eBay item', [
                'ebay_item_id' => $this->ebayItemId,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Let Laravel's queue system handle retries
        }
    }

    private function handleError(array $error): void
    {
        Log::error('eBay API error while syncing item', [
            'ebay_item_id' => $this->ebayItemId,
            'error_code' => $error['code'] ?? 'UNKNOWN',
            'error_message' => $error['message'] ?? 'Unknown error',
            'retryable' => $error['retryable'] ?? false,
        ]);

        // If the error is retryable, throw an exception to trigger Laravel's retry
        if ($error['retryable'] ?? false) {
            throw new \RuntimeException($error['message'] ?? 'API error');
        }

        // For non-retryable errors, just log and don't retry
        $this->fail(new \RuntimeException($error['message'] ?? 'Non-retryable API error'));
    }

    private function persistItemData(array $itemData): void
    {
        // This is where you would save to your local database
        // For example:
        // 
        // Product::updateOrCreate(
        //     ['ebay_item_id' => $this->ebayItemId],
        //     [
        //         'title' => $itemData['title'],
        //         'price' => $itemData['price']['value'],
        //         'currency' => $itemData['price']['currency'],
        //         'condition' => $itemData['condition'],
        //         'seller' => $itemData['seller']['username'],
        //         'url' => $itemData['itemWebUrl'],
        //         'raw_data' => json_encode($itemData),
        //         'last_synced_at' => now(),
        //     ]
        // );

        Log::debug('Item data ready for persistence', [
            'ebay_item_id' => $this->ebayItemId,
            'data_keys' => array_keys($itemData),
        ]);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('eBay item sync job failed permanently', [
            'ebay_item_id' => $this->ebayItemId,
            'local_product_id' => $this->localProductId,
            'error' => $exception->getMessage(),
        ]);

        // Optionally notify admins or mark the product as needing attention
    }
}
