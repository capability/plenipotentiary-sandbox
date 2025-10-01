<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiCrudGatewayContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Repository\CampaignRepositoryContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;

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
        ApiCrudGatewayContract $gateway,
    ) {
        $local = $repository->find(3);
        if (! $local) {
            $this->error('Local Campaign with id=3 not found.');

            return Command::FAILURE;
        }

        $status = strtoupper((string) $local->status);
        $statusMap = [
            'ACTIVE' => 'ENABLED',
            'ENABLED' => 'ENABLED',
            'PAUSED' => 'PAUSED',
            'REMOVED' => 'REMOVED',
        ];

        $providerContext = GoogleAdsDefaults::apply([
            'resourceName' => $local->resource_name,
        ]);

        $payload = CampaignCanonicalDTO::fromArray([
            [],
        ]);

        $createResult = $gateway->create($payload);

        if ($createResult->isInvalid()) {
            $payload = $createResult->toArray()['payload'] ?? [];
            $this->displayViolations('Remote API rejected the request', $payload['violations'] ?? []);

            if (isset($payload['expected'])) {
                $this->line('Expected DTO shape:');
                $this->line(json_encode($payload['expected'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }

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
