<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\GetItemAction;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\GetItemDetails\GetItemDetailsOperation;

/**
 * Laravel command for retrieving eBay item details using the Operation pattern.
 * 
 * Usage examples:
 *   php artisan ebay:get-item "v1|272535166916|0"
 *   php artisan ebay:get-item "v1|272535166916|0" --compact
 *   php artisan ebay:get-item "v1|272535166916|0" --with-product
 */
class GetItemCommand extends Command
{
    protected $signature = 'ebay:get-item
                            {item-id : The eBay item ID}
                            {--compact : Get compact response for change detection}
                            {--with-product : Include product information}
                            {--json : Output as JSON}';

    protected $description = 'Get detailed information about an eBay item';

    public function handle(GetItemAction $getItemAction): int
    {
        $itemId = $this->argument('item-id');
        $this->info("Fetching item: {$itemId}");

        try {
            // Create the appropriate operation based on options
            if ($this->option('compact')) {
                $operation = GetItemDetailsOperation::compact($itemId);
            } elseif ($this->option('with-product')) {
                $operation = GetItemDetailsOperation::withProduct($itemId);
            } else {
                $operation = new GetItemDetailsOperation($itemId);
            }
        } catch (\InvalidArgumentException $e) {
            $this->error("Invalid item ID: {$e->getMessage()}");
            return Command::FAILURE;
        }

        // Execute request
        $result = $getItemAction->execute($operation);

        if ($result->isErr()) {
            $error = $result->unwrapErr();
            $this->error("Failed to fetch item: {$error['message']}");
            return Command::FAILURE;
        }

        $dto = $result->unwrap();

        // Output as JSON if requested
        if ($this->option('json')) {
            $this->line(json_encode($dto->toArray(), JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        // Display formatted item details using DTO methods
        $this->newLine();
        $this->info("=== Item Details ===");
        $this->newLine();
        
        $this->line("Title: {$dto->title}");
        $this->line("Item ID: {$dto->itemId}");
        $this->line("Price: {$dto->getFormattedPrice()}");
        
        if ($dto->condition) {
            $this->line("Condition: {$dto->condition}" . ($dto->isNew() ? ' (New)' : ''));
        }
        
        if ($dto->seller) {
            $username = $dto->getSellerUsername();
            $feedback = $dto->getSellerFeedbackPercentage();
            $this->line("Seller: {$username}" . ($feedback ? " ({$feedback}% positive)" : ''));
        }
        
        if ($dto->hasFreeShipping()) {
            $this->line("✓ Free shipping available");
        } elseif ($cost = $dto->getCheapestShippingCost()) {
            $this->line("Shipping: {$cost['value']} {$cost['currency']}");
        }
        
        if ($dto->acceptsReturns()) {
            $days = $dto->getReturnPeriodDays();
            $this->line("✓ Returns accepted" . ($days ? " ({$days} days)" : ''));
        }
        
        if ($dto->itemWebUrl) {
            $this->line("URL: {$dto->itemWebUrl}");
        }
        
        $this->newLine();

        return Command::SUCCESS;
    }
}
