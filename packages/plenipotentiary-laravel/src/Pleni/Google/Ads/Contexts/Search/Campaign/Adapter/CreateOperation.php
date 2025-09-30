<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter;

use Google\Ads\GoogleAds\V21\Common\ManualCpc;
use Google\Ads\GoogleAds\V21\Enums\AdvertisingChannelTypeEnum\AdvertisingChannelType;
use Google\Ads\GoogleAds\V21\Enums\CampaignStatusEnum\CampaignStatus;
use Google\Ads\GoogleAds\V21\Enums\ResponseContentTypeEnum\ResponseContentType;
use Google\Ads\GoogleAds\V21\Resources\Campaign;
use Google\Ads\GoogleAds\V21\Services\CampaignOperation;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsRequest;
use Google\Ads\GoogleAds\V21\Services\MutateCampaignsResponse;
use InvalidArgumentException;
use Plenipotentiary\Laravel\Contracts\Adapter\OperationContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\Adapter\CreateSupport\CreateBudgetOperation;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsDefaults;
use Plenipotentiary\Laravel\Support\InputSpecValidator;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

final class CreateOperation implements OperationContract
{
    public const INPUT_SPEC = [
        'name' => [
            'rules' => ['required', 'string', 'min:1', 'max:128'],
        ],
        'status' => [
            'rules' => ['nullable', 'in:ENABLED,PAUSED,REMOVED'],
            'default' => 'PAUSED',
        ],
        'budgetMicros' => [
            'rules' => ['nullable', 'numeric', 'min:0'],
        ],
        'budgetResourceName' => [
            'rules' => ['nullable', 'string'],
        ],
        'providerContext.google.customerId' => [
            'rules' => ['required', 'string'],
            'source' => 'env:GOOGLE_ADS_LINKED_CUSTOMER_ID',
        ],
    ];

    public function __construct(
        private ProviderClientContract $client,
        private LoggerInterface $logger,
        private CreateBudgetOperation $createBudgetOperation,
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
        $dto->status ??= self::INPUT_SPEC['status']['default'];

        $dto->setProviderContext(GoogleAdsDefaults::apply($dto->providerContext));
        $customerId = $dto->getProviderContextValue('google.customerId');

        $violations = $this->validateAgainstSpec($dto, $customerId);
        if ($violations) {
            return $this->invalidResult($violations);
        }

        if (! $customerId) {
            // Should be caught by validation, but guard in case of future changes.
            return $this->invalidResult([
                $this->violation('providerContext.google.customerId', 'required', null, 'Google Ads customer id is required.'),
            ]);
        }

        $budgetResourceName = $dto->budgetResourceName;
        if (! $budgetResourceName) {
            $budgetMicros = (int) $dto->budgetMicros;
            if ($budgetMicros <= 0) {
                throw new InvalidArgumentException('Budget micros must be greater than zero when no budget resource name is supplied.');
            }

            $budgetResourceName = $this->createBudgetOperation->create($customerId, $dto, $budgetMicros, $validateOnly);
        }

        $campaign = new Campaign([
            'name' => $dto->name,
            'status' => CampaignStatus::value($dto->status ?? 'PAUSED'),
            'advertising_channel_type' => AdvertisingChannelType::SEARCH,
            'manual_cpc' => new ManualCpc,
            'campaign_budget' => $budgetResourceName,
        ]);

        $operation = (new CampaignOperation)->setCreate($campaign);

        $request = (new MutateCampaignsRequest)
            ->setCustomerId($customerId)
            ->setOperations([$operation])
            ->setValidateOnly($validateOnly)
            ->setResponseContentType(ResponseContentType::MUTABLE_RESOURCE);

        $this->logger->info('Creating Google Ads campaign', [
            'customerId' => $customerId,
            'name' => $dto->name,
            'validateOnly' => $validateOnly,
        ]);

        $response = $this->client
            ->raw()
            ->getCampaignServiceClient()
            ->mutateCampaigns($request);

        if ($validateOnly) {
            return Result::ok();
        }

        return Result::ok($this->responseMapper($response, $dto, $customerId));
    }

    private function validateAgainstSpec(CampaignCanonicalDTO $dto, ?string $customerId): array
    {
        $payload = [
            'name' => $dto->name,
            'status' => $dto->status,
            'budgetMicros' => $dto->budgetMicros,
            'budgetResourceName' => $dto->budgetResourceName,
            'providerContext.google.customerId' => $customerId,
        ];

        $violations = InputSpecValidator::validate(self::INPUT_SPEC, $payload);

        if (! $dto->budgetResourceName && $dto->budgetMicros === null) {
            $violations[] = $this->violation(
                'budgetMicros',
                'required_without:budgetResourceName',
                null,
                'Provide budgetMicros or an existing budgetResourceName.'
            );
        }

        return $violations;
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
        return Result::invalid(array_merge([
            [
                'field' => '_expected',
                'rule' => 'structure',
                'expected' => $this->expectedStructure(),
            ],
        ], $violations));
    }

    private function expectedStructure(): array
    {
        $fields = [];
        $providerContext = [];

        foreach (self::INPUT_SPEC as $key => $definition) {
            $descriptor = [
                'required' => in_array('required', $definition['rules'] ?? [], true),
                'rules' => $definition['rules'] ?? [],
            ];

            if (isset($definition['default'])) {
                $descriptor['default'] = $definition['default'];
            }

            if (isset($definition['cast'])) {
                $descriptor['cast'] = $definition['cast'];
            }

            if (isset($definition['source'])) {
                $descriptor['source'] = $definition['source'];
            }

            $type = $this->inferRuleType($definition['rules'] ?? []);
            if ($type !== null) {
                $descriptor['type'] = $type;
            }

            if (str_starts_with($key, 'providerContext.')) {
                $contextKey = substr($key, strlen('providerContext.'));
                $providerContext[$contextKey] = $descriptor;
            } else {
                $fields[$key] = $descriptor;
            }
        }

        return [
            'dto' => [
                'fields' => $fields,
                'providerContext' => $providerContext,
            ],
        ];
    }

    private function inferRuleType(array $rules): ?string
    {
        foreach ($rules as $rule) {
            if ($rule === 'string') {
                return 'string';
            }
            if ($rule === 'numeric') {
                return 'numeric';
            }
            if (str_starts_with($rule, 'in:')) {
                return 'enum';
            }
        }

        return null;
    }

    private function violation(string $field, string $rule, ?string $mapsTo = null, ?string $message = null): array
    {
        return array_filter([
            'field' => $field,
            'rule' => $rule,
            'mapsTo' => $mapsTo,
            'message' => $message,
        ], static fn ($value) => $value !== null && $value !== '');
    }

    private function responseMapper(MutateCampaignsResponse $response, CampaignCanonicalDTO $source, string $customerId): CampaignCanonicalDTO
    {
        $result = $response->getResults()[0] ?? null;
        $resource = $result?->getCampaign();

        $resourceName = $resource?->getResourceName() ?? $result?->getResourceName();

        return CampaignCanonicalDTO::fromArray(array_filter([
            'externalId' => $resource ? (string) $resource->getId() : null,
            'name' => $resource?->getName() ?? $source->name,
            'status' => $resource?->getStatus() ?? $source->status,
            'budgetResourceName' => $resource?->getCampaignBudget(),
            'providerContext' => array_filter(
                array_merge(
                    $source->providerContext,
                    [
                        'google.customerId' => $customerId,
                        'resourceName' => $resourceName,
                    ]
                ),
                static fn ($value) => $value !== null && $value !== ''
            ),
        ]));
    }
}
