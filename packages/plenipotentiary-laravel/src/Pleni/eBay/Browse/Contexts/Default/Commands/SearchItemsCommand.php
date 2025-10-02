<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Actions\SearchItemsAction;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Operations\SearchItems\SearchItemsOperation;

/**
 * Laravel command for searching eBay items using the Operation pattern.
 * 
 * This demonstrates how developers would use the eBay Browse API
 * in a CLI context. The command leverages the action layer,
 * keeping the command focused on CLI concerns (input/output)
 * while the action handles business logic.
 * 
 * Usage examples:
 *   php artisan ebay:search "vintage camera"
 *   php artisan ebay:search "laptop" --category=58058 --limit=20
 *   php artisan ebay:search "shirt" --min-price=10 --max-price=50
 */
class SearchItemsCommand extends Command
{
    protected $signature = 'ebay:search
                            {query : The search keyword or phrase}
                            {--category= : Category ID to limit search}
                            {--limit=20 : Number of results to return}
                            {--min-price= : Minimum price filter}
                            {--max-price= : Maximum price filter}
                            {--sort=newlyListed : Sort order}';

    protected $description = 'Search for items on eBay';

    public function handle(SearchItemsAction $searchAction): int
    {
        $query = $this->argument('query');
        $this->info("Searching eBay for: {$query}");

        // Build the operation from command options
        $operationData = [
            'query' => $query,
            'limit' => (int) $this->option('limit'),
            'sort' => $this->option('sort'),
        ];

        // Add category if specified
        if ($categoryId = $this->option('category')) {
            $operationData['categoryIds'] = $categoryId;
            $this->line("Category: {$categoryId}");
        }

        // Build price filter if specified
        if ($minPrice = $this->option('min-price')) {
            $maxPrice = $this->option('max-price') ?? '*';
            $operationData['filter'] = "price:[{$minPrice}..{$maxPrice}]";
            $this->line("Price range: \${$minPrice} - " . ($maxPrice === '*' ? 'unlimited' : "\${$maxPrice}"));
        }

        try {
            // Create the operation (this validates the input)
            $operation = SearchItemsOperation::fromArray($operationData);
        } catch (\InvalidArgumentException $e) {
            $this->error("Invalid search parameters: {$e->getMessage()}");
            return Command::FAILURE;
        }

        // Execute search
        $result = $searchAction->execute($operation);

        if ($result->isErr()) {
            $error = $result->unwrapErr();
            $this->error("Search failed: {$error['message']}");
            return Command::FAILURE;
        }

        // Display results using the DTO
        $dto = $result->unwrap();
        
        $this->newLine();
        $this->info("Found {$dto->total} items (showing " . count($dto->items) . "):");
        $this->line("Page {$dto->getCurrentPage()} of {$dto->getTotalPages()}");
        $this->newLine();

        foreach ($dto->items as $item) {
            $this->line("• {$item['title']}");
            $this->line("  Price: {$item['price']['value']} {$item['price']['currency']}");
            $this->line("  URL: {$item['itemWebUrl']}");
            $this->newLine();
        }

        // Show pagination hint
        if ($dto->hasMoreResults()) {
            $nextOffset = $dto->getNextOffset();
            $this->line("→ More results available. Use --offset={$nextOffset} to see next page");
        }

        return Command::SUCCESS;
    }
}
