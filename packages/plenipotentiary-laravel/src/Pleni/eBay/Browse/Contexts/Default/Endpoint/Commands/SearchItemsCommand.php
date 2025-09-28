<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Endpoint\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Contexts\Default\Endpoint\Actions\SearchItemsAction;
use Plenipotentiary\Laravel\Pleni\Support\Result;

class SearchItemsCommand extends Command
{
    protected $signature = 'ebay:search-items 
                            {query : Search query}
                            {--limit=50 : Maximum number of results}
                            {--categories= : Comma-separated category IDs}
                            {--sort= : Sort order (e.g., "price", "newlyListed")}
                            {--condition= : Item condition filter}
                            {--min-price= : Minimum price filter}
                            {--max-price= : Maximum price filter}
                            {--validate-only : Validate without making actual API call}';

    protected $description = 'Search for items on eBay';

    public function handle(): int
    {
        $query = $this->argument('query');
        
        $filters = [
            'limit' => (int) $this->option('limit'),
        ];

        // Parse categories
        if ($categories = $this->option('categories')) {
            $filters['categories'] = array_map('trim', explode(',', $categories));
        }

        // Add sorting
        if ($sort = $this->option('sort')) {
            $filters['sort'] = $sort;
        }

        // Add condition filter
        if ($condition = $this->option('condition')) {
            $filters['condition'] = $condition;
        }

        // Add price range
        if ($minPrice = $this->option('min-price')) {
            $filters['price_range']['min'] = $minPrice;
        }
        if ($maxPrice = $this->option('max-price')) {
            $filters['price_range']['max'] = $maxPrice;
        }

        $this->info("Searching eBay for: {$query}");
        
        if ($this->option('validate-only')) {
            $this->warn('Running in validate-only mode (dry run)');
        }

        $result = SearchItemsAction::run($query, $filters);

        return $this->handleResult($result);
    }

    private function handleResult(Result $result): int
    {
        if ($result->isOk()) {
            $data = $result->unwrap();
            
            $this->info('✅ Search completed successfully!');
            $this->newLine();
            
            if (isset($data['itemSummaries']) && count($data['itemSummaries']) > 0) {
                $this->info('Found ' . count($data['itemSummaries']) . ' items:');
                $this->newLine();
                
                foreach ($data['itemSummaries'] as $item) {
                    $this->line("• {$item['title']}");
                    $this->line("  Price: {$item['price']['value']} {$item['price']['currency']}");
                    $this->line("  Condition: {$item['condition']}");
                    $this->line("  URL: {$item['itemWebUrl']}");
                    $this->newLine();
                }
            } else {
                $this->info('No items found matching your search criteria.');
            }
            
            return Command::SUCCESS;
        }

        if ($result->isInvalid()) {
            $this->error('❌ Validation failed:');
            foreach ($result->violations() as $violation) {
                $this->line("   • {$violation['field']}: {$violation['message']}");
            }
            return Command::FAILURE;
        }

        if ($result->isErr()) {
            $error = $result->error();
            $this->error("❌ Error: {$error['error']}");
            if (isset($error['message'])) {
                $this->line("   Details: {$error['message']}");
            }
            return Command::FAILURE;
        }

        return Command::FAILURE;
    }
}
