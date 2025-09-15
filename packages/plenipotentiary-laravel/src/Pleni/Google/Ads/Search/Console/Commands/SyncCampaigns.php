<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Console\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\CampaignDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\User\CampaignRepository;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\User\CampaignService;
use Throwable;

class SyncCampaigns extends Command
{
    protected $signature = 'googleads:sync-campaigns {limit=1}';

    protected $description = 'Sync new Campaigns from local DB into Google Ads';

    public function __construct(
        private CampaignRepository $repo,
        private CampaignService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = max(1, (int) $this->argument('limit'));
        $this->info('→ Checking for new campaigns…');

        try {
            $newCampaigns = $this->repo->getAll(['campaign_id' => null]);
        } catch (Throwable $e) {
            $this->error("Failed to load campaigns: {$e->getMessage()}");

            return self::FAILURE;
        }

        if (count($newCampaigns) === 0) {
            $this->info('✔ No new campaigns to sync.');

            return self::SUCCESS;
        }

        foreach (array_slice($newCampaigns, 0, $limit) as $model) {
            $dto = CampaignDomainData::fromModel($model);

            try {
                $remoteDto = $this->service->create($dto);
                $this->repo->findOrCreateFromDto($remoteDto);

                $this->info("✔ Synced campaign '{$dto->name}' for customer {$dto->customerId}");
            } catch (Throwable $e) {
                $this->error("✘ Error syncing campaign '{$dto->name}' for customer {$dto->customerId}: {$e->getMessage()}");
            }
        }

        $this->info('✔ Campaign sync finished.');

        return self::SUCCESS;
    }
}
