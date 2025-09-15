<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Console\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\User\AdRepository;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\User\AdService;
use Throwable;

class SyncAds extends Command
{
    protected $signature = 'googleads:sync-ads {limit=1}';

    protected $description = 'Sync new Ads from local DB into Google Ads API';

    public function __construct(
        private AdRepository $repo,
        private AdService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = max(1, (int) $this->argument('limit'));

        $this->info('→ Fetching new Ads to sync...');

        try {
            $newAds = $this->repo->getNewForSync($limit);
        } catch (Throwable $e) {
            $this->error("Failed to fetch new Ads: {$e->getMessage()}");

            return self::FAILURE;
        }

        if (count($newAds) === 0) {
            $this->info('✔ No Ads to sync.');

            return self::SUCCESS;
        }

        foreach ($newAds as $model) {
            $dto = AdDomainData::fromModel($model);

            try {
                $remoteDto = $this->service->create($dto);
                $this->repo->findOrCreateFromDto($remoteDto);

                $headline = $dto->headlines[0]['text'] ?? 'unnamed';
                $this->info("✔ Synced Ad '{$headline}' for customer {$dto->customerId}");
            } catch (Throwable $e) {
                $headline = $dto->headlines[0]['text'] ?? 'unnamed';
                $this->error("✘ Error syncing Ad '{$headline}' for customer {$dto->customerId}: {$e->getMessage()}");
            }
        }

        $this->info('✔ Ads sync finished.');

        return self::SUCCESS;
    }
}
