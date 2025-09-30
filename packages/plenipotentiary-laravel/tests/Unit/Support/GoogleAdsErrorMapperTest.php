<?php

use Google\Ads\GoogleAds\Lib\V21\GoogleAdsException;
use Google\Ads\GoogleAds\V21\Errors\AuthenticationErrorEnum\AuthenticationError;
use Google\Ads\GoogleAds\V21\Errors\ErrorCode;
use Google\Ads\GoogleAds\V21\Errors\ErrorLocation;
use Google\Ads\GoogleAds\V21\Errors\ErrorLocation\FieldPathElement;
use Google\Ads\GoogleAds\V21\Errors\FieldErrorEnum\FieldError;
use Google\Ads\GoogleAds\V21\Errors\GoogleAdsError;
use Google\Ads\GoogleAds\V21\Errors\GoogleAdsFailure;
use Google\Ads\GoogleAds\V21\Errors\QuotaErrorEnum\QuotaError;
use Google\Ads\GoogleAds\V21\Errors\RequestErrorEnum\RequestError;
use Google\ApiCore\ApiException;
use Plenipotentiary\Laravel\Exceptions\DomainException;
use Plenipotentiary\Laravel\Exceptions\DomainInvalidException;
use Plenipotentiary\Laravel\Pleni\Google\Ads\Shared\Support\GoogleAdsErrorMapper;

function makeGoogleAdsException(array $errors, string $message = 'Request failed', ?string $status = null): GoogleAdsException
{
    $failure = new GoogleAdsFailure(['errors' => $errors]);
    $apiException = new ApiException($message, 0, $status, [
        'metadata' => ['request-id' => ['abc123']],
    ]);

    return new GoogleAdsException($apiException, $failure, [
        'metadata' => ['request-id' => ['abc123']],
    ]);
}

describe('GoogleAdsErrorMapper', function () {
    it('maps field errors to domain invalid exception', function () {
        $error = new GoogleAdsError([
            'message' => 'Campaign name is required',
            'location' => new ErrorLocation([
                'field_path_elements' => [
                    new FieldPathElement(['field_name' => 'campaign']),
                    new FieldPathElement(['field_name' => 'name']),
                ],
            ]),
            'error_code' => new ErrorCode(['field_error' => FieldError::REQUIRED]),
        ]);

        $mapper = new GoogleAdsErrorMapper;
        $mapped = $mapper->map(makeGoogleAdsException([$error]));

        expect($mapped)->toBeInstanceOf(DomainInvalidException::class);
        $violations = $mapped->violations();
        expect($violations)->toHaveCount(1);
        expect($violations[0]['field'])->toBe('campaign[0].name[0]');
        expect($violations[0]['rule'])->toBe('field_error');
        expect($violations[0]['message'])->toBe('Campaign name is required');
        expect($violations[0]['providerReason'] ?? null)->toBeNull();
    });

    it('maps quota errors to rate limited domain exception', function () {
        $error = new GoogleAdsError([
            'message' => 'Quota exceeded',
            'error_code' => new ErrorCode(['quota_error' => QuotaError::RESOURCE_EXHAUSTED]),
        ]);

        $mapper = new GoogleAdsErrorMapper;
        $mapped = $mapper->map(makeGoogleAdsException([$error]));

        expect($mapped)->toBeInstanceOf(DomainException::class)
            ->and($mapped->code())->toBe('RateLimited')
            ->and($mapped->httpStatus())->toBe(429)
            ->and($mapped->isRetryable())->toBeTrue()
            ->and($mapped->meta())->toHaveKey('providerReason', QuotaError::name(QuotaError::RESOURCE_EXHAUSTED));
    });

    it('maps request not found errors to domain exception', function () {
        $error = new GoogleAdsError([
            'message' => 'Resource not found',
            'error_code' => new ErrorCode(['request_error' => RequestError::RESOURCE_NOT_FOUND]),
        ]);

        $mapper = new GoogleAdsErrorMapper;
        $mapped = $mapper->map(makeGoogleAdsException([$error]));

        expect($mapped)->toBeInstanceOf(DomainException::class)
            ->and($mapped->code())->toBe('NotFound')
            ->and($mapped->httpStatus())->toBe(404)
            ->and($mapped->meta())->toHaveKey('providerReason', RequestError::name(RequestError::RESOURCE_NOT_FOUND));
    });

    it('maps authentication ApiException to AuthException', function () {
        $apiException = new ApiException('Auth failed', 0, 'UNAUTHENTICATED');
        $mapper = new GoogleAdsErrorMapper;

        $mapped = $mapper->map($apiException);

        expect($mapped)->toBeInstanceOf(\Plenipotentiary\Laravel\Exceptions\AuthException::class)
            ->and($mapped->httpStatus())->toBe(401);
    });

    it('passes through already mapped domain exceptions', function () {
        $domain = new DomainException('AuthFailed', 'auth failed', 401);
        $mapper = new GoogleAdsErrorMapper;

        expect($mapper->map($domain))->toBe($domain);
    });

    it('maps authentication failures reported in GoogleAdsError', function () {
        $error = new GoogleAdsError([
            'message' => 'OAuth token invalid',
            'error_code' => new ErrorCode(['authentication_error' => AuthenticationError::CLIENT_CUSTOMER_ID_INVALID]),
        ]);

        $mapper = new GoogleAdsErrorMapper;
        $mapped = $mapper->map(makeGoogleAdsException([$error]));

        expect($mapped)->toBeInstanceOf(\Plenipotentiary\Laravel\Exceptions\AuthException::class)
            ->and($mapped->meta())->toHaveKey('providerReason', AuthenticationError::name(AuthenticationError::CLIENT_CUSTOMER_ID_INVALID));
    });
});
