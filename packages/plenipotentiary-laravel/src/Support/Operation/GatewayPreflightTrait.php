<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Support\Operation;

use Plenipotentiary\Laravel\Contracts\Adapter\AdapterVerbContract;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Contexts\Search\Campaign\DTO\CampaignCanonicalDTO;
use Plenipotentiary\Laravel\Support\Validation\InputSpecValidator;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Trait to run a uniform preflight validation before delegating to an Operation.
 *
 * Ensures that INPUT_SPEC + DTO are validated consistently in Gateways.
 */
trait GatewayPreflightTrait
{
    /**
     * Run preflight validation for an operation DTO against its INPUT_SPEC.
     *
     * @param  OperationContract  $operation
     */
    private function preflight(AdapterVerbContract $operation, CampaignCanonicalDTO $dto): ?Result
    {
        $preflight = InputSpecValidator::validate($operation::inputSpec(), $dto->toArray());

        if (! empty($preflight['violations'])) {
            return Result::invalid($preflight['violations'], $preflight['expected']);
        }

        return null;
    }
}
