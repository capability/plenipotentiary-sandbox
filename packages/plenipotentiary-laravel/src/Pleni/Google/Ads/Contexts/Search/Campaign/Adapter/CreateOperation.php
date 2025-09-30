<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Enums\BudgetDeliveryMethodEnum\BudgetDeliveryMethod;
use Google\Ads\GoogleAds\V21\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V21\Enums\ResponseContentTypeEnum\ResponseContentType;
use Google\Ads\GoogleAds\V21\Resources\Campaign;
use Google\Ads\GoogleAds\V21\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V21\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateGoogleAdsResponse;
use Google\Ads\GoogleAds\V21\Services\MutateOperation;
use InvalidArgumentException;
use Plenipotentiary\Laravel\Contracts\Adapter\OperationContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Pleni\Support\CanonicalFactory;
use Plenipotentiary\Laravel\Pleni\Support\InputSpecValidator;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class CreateOperation implements OperationContract
{
    public const INPUT_SPEC = [
        'name' => [
            'key' => 'name',
            'rules' => ['required', 'string', 'min:1', 'max:128'],
            'mapsTo' => 'campaign.name',
        ],
        'status' => [
            'key' => 'status',
            'rules' => ['required', 'in:ENABLED,PAUSED,REMOVED'],
            'mapsTo' => 'campaign.status',
        ],
        'budgetResourceName' => [
            'key' => 'budget_resource_name',
            'rules' => ['nullable', 'string'],
            'mapsTo' => 'campaign.campaign_budget',
        ],
        'budgetMicros' => [
            'key' => 'budget',
            'rules' => ['nullable', 'numeric', 'min:0'],
            'mapsTo' => 'campaign_budget.amount_micros',
        ],
        'cpcBidMicros' => [
            'key' => 'cpc_bid',
            'rules' => ['nullable', 'numeric', 'min:0'],
            'cast' => 'int',
            'mapsTo' => 'campaign.manual_cpc',
        ],
        'customerId' => [
            'key' => 'customer_id',
            'rules' => ['nullable', 'string'],
            'mapsTo' => 'google.customerId',
        ],
    ];

    public function __construct(
        private ProviderClientContract $client,
        private LoggerInterface $logger,
    ) {}

    public static function inputSpec(): array
    {
        return self::INPUT_SPEC;
    }

    public function perform(object $payload, bool $validateOnly = false): Result
    {
        if (! $payload instanceof CampaignCanonicalDTO) {
            return $this->invalidPayloadResult($payload);
        }

        $dto = $payload;

        // --- Step 1: validate canonical inputs using the spec so scaffolding stays in sync.
        $raw = [
            'name' => $dto->name,
            'status' => $dto->status,
            'budgetResourceName' => $dto->budgetResourceName,
            'budgetMicros' => $dto->budgetMicros,
            'cpcBidMicros' => $dto->cpcBidMicros,
            'customerId' => $dto->customerId ?? $dto->providerContextValue('google.customerId'),
        ];

        $violations = InputSpecValidator::validate(self::INPUT_SPEC, $raw);
        if (! $dto->budgetResourceName && $dto->budgetMicros === null) {
            $violations[] = $this->violation(
                'budgetResourceName',
                'required_without:budgetMicros',
                'campaign.campaign_budget',
                'Provide either budgetResourceName or budgetMicros.'
            );
        }
        if ($violations) {
            return $this->invalidResult($violations);
        }

        // --- Step 2: enrich provider context so the Google Ads client knows the customer id.
        $context = $dto->providerContext();
        if ($dto->customerId && ! isset($context['google.customerId'])) {
            $context['google.customerId'] = $dto->customerId;
        }

        try {
            $context = GoogleAdsDefaults::require($context, 'google.customerId');
        } catch (InvalidArgumentException $exception) {
            return $this->invalidResult([
                $this->violation('customerId', 'required', 'google.customerId', $exception->getMessage()),
            ]);
        }

        $dto->mergeProviderContext($context);
        $customerId = $context['google.customerId'];

        // --- Step 3: build the Google Ads mutate request. Keep everything inline so onboarding devs
        // can match this code to Google’s PHP examples.
        $usesUnifiedMutate = false;
        if ($dto->budgetResourceName) {
            $campaign = new Campaign([
                'name' => $dto->name,
                'status' => CampaignStatus::value($dto->status ?? 'PAUSED'),
                'campaign_budget' => $dto->budgetResourceName,
            ]);

            $operation = (new CampaignOperation)->setCreate($campaign);

            $request = (new MutateCampaignsRequest)
                ->setCustomerId($customerId)
                ->setOperations([$operation])
                ->setValidateOnly($validateOnly)
                ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);
        } else {
            $temporaryBudgetName = sprintf('customers/%s/campaignBudgets/%d', $customerId, -1);
            $budget = new CampaignBudget([
                'name' => trim((string) ($dto->name ?? 'Campaign')).' Budget',
                'amount_micros' => $dto->budgetMicros ?? 1_000_000,
                'delivery_method' => BudgetDeliveryMethod::STANDARD,
                'resource_name' => $temporaryBudgetName,
            ]);

            $budgetOperation = (new CampaignBudgetOperation)->setCreate($budget);

            $campaign = new Campaign([
                'name' => $dto->name,
                'status' => CampaignStatus::value($dto->status ?? 'PAUSED'),
                'campaign_budget' => $temporaryBudgetName,
            ]);

            $campaignOperation = (new CampaignOperation)->setCreate($campaign);

            $request = (new MutateGoogleAdsRequest)
                ->setCustomerId($customerId)
                ->setMutateOperations([
                    (new MutateOperation)->setCampaignBudgetOperation($budgetOperation),
                    (new MutateOperation)->setCampaignOperation($campaignOperation),
                ])
                ->setValidateOnly($validateOnly)
                ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);

            $usesUnifiedMutate = true;
        }

        // --- Step 4: call the SDK and translate the response.
        $this->logger->info('Creating Google Ads campaign', [
            'name' => $dto->name,
            'customerId' => $customerId,
            'usesUnifiedMutate' => $usesUnifiedMutate,
        ]);

        $client = $this->client->raw();
        $response = $usesUnifiedMutate
            ? $client->getGoogleAdsServiceClient()->mutate($request)
            : $client->getCampaignServiceClient()->mutateCampaigns($request);

        if ($validateOnly) {
            return Result::ok();
        }

        return Result::ok($this->responseMapper($response, $dto));
    }

    private function invalidPayloadResult(object $payload): Result
    {
        return Result::invalid([
            [
                'field' => '_payload',
                'rule' => CampaignCanonicalDTO::class,
                'message' => sprintf(
                    'CreateOperation expects %s payload, received %s.',
                    CampaignCanonicalDTO::class,
                    get_debug_type($payload)
                ),
            ],
        ]);
    }

    private function invalidResult(array $violations): Result
    {
        $violations[] = [
            'field' => '_dev',
            'message' => 'Scaffold DTO/Factory from operation INPUT_SPEC',
            'dto_schema' => self::INPUT_SPEC,
            'dto_class' => CampaignCanonicalDTO::class,
            'factory_class' => CanonicalFactory::class,
            'artisan' => 'php artisan pleni:build-factory Provider=Google Domain=Ads Resource=Campaign --operation=CreateOperation',
        ];

        return Result::invalid($violations);
    }

    private function violation(string $field, string $rule, ?string $mapsTo = null, ?string $message = null): array
    {
        return array_filter([
            'field' => $field,
            'rule' => $rule,
            'mapsTo' => $mapsTo,
            'message' => $message,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Provider response ➜ canonical DTO so callers always receive the same shape back.
     */
    public function responseMapper(MutateCampaignsResponse|MutateGoogleAdsResponse $response, CampaignCanonicalDTO $source): CampaignCanonicalDTO
    {
        if ($response instanceof MutateCampaignsResponse) {
            $result = $response->getResults()[0] ?? null;
            $resource = $result?->getCampaign();

            return CampaignCanonicalDTO::fromArray([
                'externalId' => $resource ? (string) $resource->getId() : null,
                'name' => $resource?->getName(),
                'status' => $resource?->getStatus(),
                'budgetResourceName' => $resource?->getCampaignBudget(),
                'identifiers' => array_filter([
                    'resourceName' => $resource?->getResourceName() ?? $result?->getResourceName(),
                ], fn ($value) => $value !== null && $value !== ''),
                'customerId' => $source->customerId,
                'providerContext' => $source->providerContext(),
            ]);
        }

        $resourceName = null;
        foreach ($response->getMutateOperationResponses() as $operationResponse) {
            if ($operationResponse->hasCampaignResult()) {
                $resourceName = $operationResponse->getCampaignResult()->getResourceName();
                break;
            }
        }

        return CampaignCanonicalDTO::fromArray([
            'name' => $source->name,
            'status' => $source->status,
            'budgetResourceName' => $source->budgetResourceName,
            'identifiers' => array_filter([
                'resourceName' => $resourceName,
            ], fn ($value) => $value !== null && $value !== ''),
            'customerId' => $source->customerId,
            'providerContext' => $source->providerContext(),
        ]);
    }
}
