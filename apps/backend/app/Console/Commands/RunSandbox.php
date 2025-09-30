<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\CampaignRepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\CanonicalFactory;
use Plenipotentiary\Laravel\Support\InputSource\ArraySource;

class RunSandbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-sandbox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demonstrate syncing a local Campaign (id=3) to Google Ads.';

    /**
     * Execute the console command.
     */
    public function handle(
        CampaignRepositoryContract $repository,
        CanonicalFactory $factory,
        ApiCrudGatewayContract $gateway,
    ) {
        $local = $repository->find(3);
        if (! $local) {
            $this->error('Local Campaign with id=3 not found.');

            return Command::FAILURE;
        }

        $providerContext = GoogleAdsDefaults::apply([
            'resourceName' => $local->resource_name,
        ]);

        $customerId = $providerContext['google.customerId'] ?? null;
        if (! $customerId) {
            $this->error('Missing google.customerId. Set GOOGLE_ADS_LINKED_CUSTOMER_ID before running the sandbox.');

            return Command::FAILURE;
        }

        $status = strtoupper((string) $local->status);
        $statusMap = [
            'ACTIVE' => 'ENABLED',
            'ENABLED' => 'ENABLED',
            'PAUSED' => 'PAUSED',
            'REMOVED' => 'REMOVED',
        ];
        $status = $statusMap[$status] ?? 'PAUSED';

        $dtoResult = $factory->make(
            CampaignCanonicalDTO::class,
            [
                new ArraySource([
                    'internal_id' => (string) $local->id,
                    'name' => $local->name,
                    'status' => $status,
                    'budget_resource_name' => $local->budget_resource_name,
                    'budget' => $local->daily_budget,
                ]),
            ],
            [
                'providerContext' => array_filter(
                    array_merge($providerContext, ['resourceName' => $local->resource_name]),
                    fn ($value) => $value !== null && $value !== ''
                ),
            ]
        );

        if ($dtoResult->isInvalid()) {
            $this->displayViolations('Local campaign failed canonical validation', $dtoResult->violations() ?? []);

            return Command::FAILURE;
        }

        if ($dtoResult->isErr()) {
            $this->displayError('Unable to build canonical campaign DTO', $dtoResult->error() ?? []);

            return Command::FAILURE;
        }

        /** @var CampaignCanonicalDTO $payload */
        $payload = $dtoResult->unwrap();

        $createResult = $gateway->create($payload);

        if ($createResult->isInvalid()) {
            $this->displayViolations('Remote API rejected the request', $createResult->violations() ?? []);

            return Command::FAILURE;
        }

        if ($createResult->isErr()) {
            $this->displayError('Remote API returned an error', $createResult->error() ?? []);

            return Command::FAILURE;
        }

        /** @var CampaignCanonicalDTO $remote */
        $remote = $createResult->unwrap();

        $resourceId = $remote->externalId;
        if (is_string($resourceId) && ctype_digit($resourceId)) {
            $resourceId = (int) $resourceId;
        }

        $repository->update($local->id, [
            'resource_id' => $resourceId,
            'resource_name' => $remote->getProviderContextValue('resourceName'),
            'name' => $remote->name ?? $local->name,
        ]);

        $resourceName = $remote->getProviderContextValue('resourceName') ?? 'unknown-resource';
        $this->info("✅ Synced campaign ID {$local->id} to remote: {$resourceName}");

        return Command::SUCCESS;
    }

    /**
     * @param  array<int,array<string,mixed>>  $violations
     */
    private function displayViolations(string $message, array $violations): void
    {
        $this->error($message);

        foreach ($violations as $violation) {
            $field = $violation['field'] ?? 'unknown';
            $rule = $violation['rule'] ?? 'rule';
            $extra = $violation['message'] ?? $violation['mapsTo'] ?? '';

            $this->line(sprintf(' - %s (%s)%s', $field, $rule, $extra ? ": {$extra}" : ''));
        }
    }

    private function displayError(string $message, array $error): void
    {
        $this->error($message);
        if (! empty($error)) {
            $json = json_encode($error, JSON_PRETTY_PRINT);
            if ($json === false) {
                $json = var_export($error, true);
            }

            $this->line(' - '.$json);
        }
    }
}
