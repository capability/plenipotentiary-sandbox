<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Console\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\DTO\Domain\AdGroupDomainData;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Repository\User\AdGroupRepository;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Search\Service\User\AdGroupService;
use Throwable;

class SyncAdGroups extends Command
{
    protected $signature = 'googleads:sync-adgroups {limit=1}';

    protected $description = 'Sync new AdGroups from local DB into Google Ads API';

    public function __construct(
        private AdGroupRepository $repo,
        private AdGroupService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = max(1, (int) $this->argument('limit'));

        $this->info('→ Fetching new AdGroups to sync...');

        try {
            $newAdGroups = $this->repo->getNewForSync($limit);
        } catch (Throwable $e) {
            $this->error("Failed to fetch new AdGroups: {$e->getMessage()}");

            return self::FAILURE;
        }

        if (count($newAdGroups) === 0) {
            $this->info('✔ No AdGroups to sync.');

            return self::SUCCESS;
        }

        foreach ($newAdGroups as $model) {
            $dto = AdGroupDomainData::fromModel($model);

            try {
                $remoteDto = $this->service->create($dto);
                $this->repo->findOrCreateFromDto($remoteDto);

                $this->info("✔ Synced AdGroup '{$dto->name}' for customer {$dto->customerId}");
            } catch (Throwable $e) {
                $this->error("✘ Error syncing AdGroup '{$dto->name}' for customer {$dto->customerId}: {$e->getMessage()}");
            }
        }

        $this->info('✔ AdGroup sync finished.');

        return self::SUCCESS;
    }
}
