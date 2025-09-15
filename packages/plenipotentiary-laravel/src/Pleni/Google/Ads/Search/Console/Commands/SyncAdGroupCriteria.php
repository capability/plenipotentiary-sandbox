<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Console\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupCriterionDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\User\AdGroupCriterionRepository;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\User\AdGroupCriterionService;
use Throwable;

class SyncAdGroupCriteria extends Command
{
    protected $signature = 'googleads:sync-adgroup-criteria {limit=1}';

    protected $description = 'Sync new Ad Group Criteria from local DB into Google Ads API';

    public function __construct(
        private AdGroupCriterionRepository $repo,
        private AdGroupCriterionService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = max(1, (int) $this->argument('limit'));

        $this->info('→ Fetching new Ad Group Criteria to sync...');

        try {
            $newCriteria = $this->repo->getNewForSync($limit);
        } catch (Throwable $e) {
            $this->error("Failed to fetch Ad Group Criteria: {$e->getMessage()}");

            return self::FAILURE;
        }

        if (count($newCriteria) === 0) {
            $this->info('✔ No Ad Group Criteria to sync.');

            return self::SUCCESS;
        }

        foreach ($newCriteria as $model) {
            $dto = AdGroupCriterionDomainData::fromModel($model);

            try {
                $remoteDto = $this->service->create($dto);
                $this->repo->findOrCreateFromDto($remoteDto);

                $this->info("✔ Synced Criterion '{$dto->criterionId}' in AdGroup {$dto->parentAdGroupResourceName} for customer {$dto->customerId}");
            } catch (Throwable $e) {
                $this->error("✘ Error syncing Criterion in AdGroup {$dto->parentAdGroupResourceName} for customer {$dto->customerId}: {$e->getMessage()}");
            }
        }

        $this->info('✔ Ad Group Criteria sync finished.');

        return self::SUCCESS;
    }
}
