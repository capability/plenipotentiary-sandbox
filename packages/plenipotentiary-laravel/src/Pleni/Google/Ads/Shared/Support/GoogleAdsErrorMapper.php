<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support;

use Google\Ads\GoogleAds\Lib\V21\GoogleAdsException;
use Google\Ads\GoogleAds\V21\Errors\AuthenticationErrorEnum\AuthenticationError;
use Google\Ads\GoogleAds\V21\Errors\AuthorizationErrorEnum\AuthorizationError;
use Google\Ads\GoogleAds\V21\Errors\CampaignErrorEnum\CampaignError;
use Google\Ads\GoogleAds\V21\Errors\GoogleAdsError;
use Google\Ads\GoogleAds\V21\Errors\GoogleAdsFailure;
use Google\Ads\GoogleAds\V21\Errors\OperationAccessDeniedErrorEnum\OperationAccessDeniedError;
use Google\Ads\GoogleAds\V21\Errors\QuotaErrorEnum\QuotaError;
use Google\Ads\GoogleAds\V21\Errors\RequestErrorEnum\RequestError;
use Google\Ads\GoogleAds\V21\Errors\ResourceAccessDeniedErrorEnum\ResourceAccessDeniedError;
use Google\ApiCore\ApiException;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Exceptions\AuthException;
use Plenipotentiary\Laravel\Exceptions\DomainException;
use Plenipotentiary\Laravel\Exceptions\DomainInvalidException;
use Plenipotentiary\Laravel\Exceptions\PermissionException;
use Plenipotentiary\Laravel\Exceptions\TransportException;
use Throwable;

final class GoogleAdsErrorMapper implements ErrorMapperContract
{
    /** @var array<string,bool> */
    private const INPUT_ERROR_CATEGORIES = [
        'field_error' => true,
        'policy_finding_error' => true,
        'policy_violation_error' => true,
        'string_format_error' => true,
        'string_length_error' => true,
        'enum_error' => true,
        'range_error' => true,
        'date_error' => true,
        'date_range_error' => true,
        'mutate_error' => true,
        'customer_error' => true,
    ];

    /** @var array<int,bool> */
    private const REQUEST_ERROR_INPUT_CODES = [
        RequestError::RESOURCE_NAME_MISSING => true,
        RequestError::RESOURCE_NAME_MALFORMED => true,
        RequestError::BAD_RESOURCE_ID => true,
        RequestError::INVALID_CUSTOMER_ID => true,
        RequestError::OPERATION_REQUIRED => true,
        RequestError::INVALID_PAGE_TOKEN => true,
        RequestError::EXPIRED_PAGE_TOKEN => true,
        RequestError::INVALID_PAGE_SIZE => true,
        RequestError::REQUIRED_FIELD_MISSING => true,
        RequestError::IMMUTABLE_FIELD => true,
        RequestError::CANNOT_MODIFY_FOREIGN_FIELD => true,
        RequestError::INVALID_ENUM_VALUE => true,
        RequestError::DEVELOPER_TOKEN_PARAMETER_MISSING => true,
        RequestError::LOGIN_CUSTOMER_ID_PARAMETER_MISSING => true,
        RequestError::VALIDATE_ONLY_REQUEST_HAS_PAGE_TOKEN => true,
        RequestError::CANNOT_RETURN_SUMMARY_ROW_FOR_REQUEST_WITHOUT_METRICS => true,
        RequestError::CANNOT_RETURN_SUMMARY_ROW_FOR_VALIDATE_ONLY_REQUESTS => true,
        RequestError::INCONSISTENT_RETURN_SUMMARY_ROW_VALUE => true,
        RequestError::TOTAL_RESULTS_COUNT_NOT_ORIGINALLY_REQUESTED => true,
    ];

    /** @var array<int,bool> */
    private const REQUEST_ERROR_NOT_FOUND_CODES = [
        RequestError::RESOURCE_NOT_FOUND => true,
    ];

    public function map(Throwable $e): Throwable
    {
        if ($e instanceof DomainException) {
            return $e; // already mapped upstream
        }

        if ($e instanceof GoogleAdsException) {
            return $this->mapGoogleAdsException($e);
        }

        if ($e instanceof ApiException) {
            return $this->mapApiException($e);
        }

        return new DomainException(
            'ProviderError',
            $e->getMessage() ?: 'Google Ads request failed.',
            500,
            false,
            ['provider' => 'google_ads'],
            $e
        );
    }

    private function mapGoogleAdsException(GoogleAdsException $exception): Throwable
    {
        $meta = array_filter([
            'provider' => 'google_ads',
            'requestId' => $exception->getRequestId(),
        ]);

        $errors = $this->failureErrors($exception->getGoogleAdsFailure());
        if ($errors === []) {
            return new DomainException(
                'ProviderError',
                $exception->getMessage() ?: 'Google Ads request failed.',
                502,
                true,
                $meta,
                $exception
            );
        }

        if ($this->allInputErrors($errors)) {
            return new DomainInvalidException(
                $this->toViolations($errors),
                $meta,
                $exception
            );
        }

        return $this->classifyToException($errors, $meta, $exception);
    }

    /**
     * @return array<int,GoogleAdsError>
     */
    private function failureErrors(?GoogleAdsFailure $failure): array
    {
        if ($failure === null) {
            return [];
        }

        $errors = [];
        foreach ($failure->getErrors() as $error) {
            if ($error instanceof GoogleAdsError) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    private function mapApiException(ApiException $exception): Throwable
    {
        $status = $exception->getStatus() ?: null;
        $reason = $exception->getReason();
        $meta = array_filter([
            'provider' => 'google_ads',
            'status' => $status,
            'reason' => $reason,
        ]);

        return match ($status) {
            'UNAUTHENTICATED' => new AuthException(
                $exception->getMessage() ?: 'Google Ads authentication failed.',
                $meta,
                $exception
            ),
            'PERMISSION_DENIED' => new PermissionException(
                $exception->getMessage() ?: 'Google Ads permission denied.',
                $meta,
                $exception
            ),
            'RESOURCE_EXHAUSTED' => new DomainException(
                'RateLimited',
                $exception->getMessage() ?: 'Google Ads rate limit exceeded.',
                429,
                true,
                $meta,
                $exception
            ),
            'DEADLINE_EXCEEDED', 'UNAVAILABLE', 'ABORTED' => new TransportException(
                $exception->getMessage() ?: 'Google Ads service temporarily unavailable.',
                true,
                $meta,
                $exception
            ),
            'INVALID_ARGUMENT', 'FAILED_PRECONDITION' => new DomainInvalidException(
                [[
                    'field' => null,
                    'message' => $exception->getMessage() ?: 'Invalid Google Ads request.',
                ]],
                $meta,
                $exception
            ),
            default => new DomainException(
                'ProviderError',
                $exception->getMessage() ?: 'Google Ads transport error.',
                502,
                true,
                $meta,
                $exception
            ),
        };
    }

    private function classifyToException(array $errors, array $meta, GoogleAdsException $exception): Throwable
    {
        /** @var GoogleAdsError $primary */
        $primary = $errors[0];
        $category = $this->errorCategory($primary);
        $reason = $this->extractReason($primary);
        $message = trim($primary->getMessage()) ?: 'Google Ads request failed.';
        $meta = array_filter(array_merge($meta, [
            'providerCategory' => $category,
            'providerReason' => $reason,
        ]));

        return match ($category) {
            'authentication_error' => new AuthException($message, $meta, $exception),
            'authorization_error',
            'operation_access_denied_error',
            'resource_access_denied_error' => new PermissionException($message, $meta, $exception),
            'quota_error',
            'resource_count_limit_exceeded_error' => new DomainException(
                'RateLimited',
                $message,
                429,
                true,
                $meta,
                $exception
            ),
            'request_error' => $this->mapRequestError($primary, $meta, $exception),
            'campaign_error' => new DomainException(
                'InvalidState',
                $message,
                409,
                false,
                $meta,
                $exception
            ),
            'policy_finding_error',
            'policy_violation_error' => new DomainException(
                'PolicyViolation',
                $message,
                422,
                false,
                $meta,
                $exception
            ),
            'internal_error',
            'database_error' => new DomainException(
                'ProviderInternal',
                $message,
                503,
                true,
                $meta,
                $exception
            ),
            default => new DomainException(
                'ProviderError',
                $message,
                502,
                true,
                $meta,
                $exception
            ),
        };
    }

    private function mapRequestError(GoogleAdsError $error, array $meta, GoogleAdsException $exception): Throwable
    {
        $code = $error->getErrorCode()->getRequestError();
        $reason = $code !== null ? RequestError::name($code) : null;
        $meta = array_filter(array_merge($meta, ['providerReason' => $reason]));

        if ($code !== null && isset(self::REQUEST_ERROR_NOT_FOUND_CODES[$code])) {
            return new DomainException(
                'NotFound',
                $error->getMessage() ?: 'Requested Google Ads resource was not found.',
                404,
                false,
                $meta,
                $exception
            );
        }

        if ($code !== null && isset(self::REQUEST_ERROR_INPUT_CODES[$code])) {
            return new DomainInvalidException(
                $this->toViolations([$error]),
                $meta,
                $exception
            );
        }

        return new DomainException(
            'ProviderError',
            $error->getMessage() ?: 'Google Ads request error.',
            502,
            true,
            $meta,
            $exception
        );
    }

    private function allInputErrors(array $errors): bool
    {
        foreach ($errors as $error) {
            if (! $this->isInputError($error)) {
                return false;
            }
        }

        return $errors !== [];
    }

    private function isInputError(GoogleAdsError $error): bool
    {
        $category = $this->errorCategory($error);

        if (isset(self::INPUT_ERROR_CATEGORIES[$category])) {
            return true;
        }

        if ($category === 'request_error') {
            $code = $error->getErrorCode()->getRequestError();

            return $code !== null && isset(self::REQUEST_ERROR_INPUT_CODES[$code]);
        }

        return false;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function toViolations(array $errors): array
    {
        $violations = [];

        foreach ($errors as $error) {
            $violations[] = array_filter([
                'field' => $this->formatFieldPath($error),
                'rule' => $this->errorCategory($error),
                'message' => trim($error->getMessage()) ?: null,
                'providerReason' => $this->extractReason($error),
            ]);
        }

        return $violations;
    }

    private function formatFieldPath(GoogleAdsError $error): ?string
    {
        $location = $error->getLocation();
        if ($location === null) {
            return null;
        }

        $segments = [];
        foreach ($location->getFieldPathElements() as $element) {
            $segment = $element->getFieldName();
            $index = $element->getIndex();
            if ($index !== null) {
                $segment .= '['.$index.']';
            }
            $segments[] = $segment;
        }

        return $segments ? implode('.', $segments) : null;
    }

    private function errorCategory(GoogleAdsError $error): string
    {
        return (string) $error->getErrorCode()->getErrorCode();
    }

    private function extractReason(GoogleAdsError $error): ?string
    {
        $code = $error->getErrorCode();
        $category = $code->getErrorCode();

        return match ($category) {
            'authentication_error' => $code->getAuthenticationError() !== null
                ? AuthenticationError::name($code->getAuthenticationError())
                : null,
            'authorization_error' => $code->getAuthorizationError() !== null
                ? AuthorizationError::name($code->getAuthorizationError())
                : null,
            'operation_access_denied_error' => $code->getOperationAccessDeniedError() !== null
                ? OperationAccessDeniedError::name($code->getOperationAccessDeniedError())
                : null,
            'resource_access_denied_error' => $code->getResourceAccessDeniedError() !== null
                ? ResourceAccessDeniedError::name($code->getResourceAccessDeniedError())
                : null,
            'quota_error' => $code->getQuotaError() !== null
                ? QuotaError::name($code->getQuotaError())
                : null,
            'request_error' => $code->getRequestError() !== null
                ? RequestError::name($code->getRequestError())
                : null,
            'campaign_error' => $code->getCampaignError() !== null
                ? CampaignError::name($code->getCampaignError())
                : null,
            default => null,
        };
    }
}
