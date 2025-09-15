<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Ebay\Browse\ItemSummary\Service\User;

use Plenipotentiary\Laravel\Contracts\SearchResult;
use Plenipotentiary\Laravel\Contracts\SearchServiceContract;
use Plenipotentiary\Laravel\Pleni\Ebay\Browse\ItemSummary\Service\Generated\EbayBrowseItemSummaryService as GeneratedService;

/**
 * User service for eBay Browse ItemSummary operations.
 * This class delegates all functionality to the Generated service by default.
 * Extend and override behaviour here; this file will not be overwritten.
 */
class EbayBrowseItemSummaryService implements SearchServiceContract
{
    public function __construct(
        protected GeneratedService $generated
    ) {}

    public function search(string $keywords): SearchResult
    {
        return $this->generated->search($keywords);
    }
}
