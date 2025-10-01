<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Support;

use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Throwable;

/**
 * OpenAI API Error Mapper
 *
 * Maps OpenAI-specific exceptions into domain-friendly errors.
 * Handles various OpenAI API error scenarios and provides meaningful error messages.
 */
final class OpenAIErrorMapper implements ErrorMapperContract
{
    /**
     * Map provider-specific exceptions into your app's domain-friendly errors
     */
    public function map(Throwable $e): Throwable
    {
        // Handle Guzzle HTTP exceptions
        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
            return $this->mapHttpException($e);
        }

        // Handle authentication errors
        if ($e instanceof \GuzzleHttp\Exception\ClientException) {
            $statusCode = $e->getResponse()?->getStatusCode();

            if ($statusCode === 401) {
                return new \DomainException('OpenAI authentication failed. Please check your API key.', 401, $e);
            }

            if ($statusCode === 403) {
                return new \DomainException('OpenAI access forbidden. Check your API key permissions and billing.', 403, $e);
            }
        }

        // Handle rate limiting
        if ($e instanceof \GuzzleHttp\Exception\ServerException) {
            $statusCode = $e->getResponse()?->getStatusCode();

            if ($statusCode === 429) {
                return $this->mapRateLimitError($e);
            }
        }

        // For other exceptions, wrap them as domain exceptions
        return new \DomainException("OpenAI API error: {$e->getMessage()}", $e->getCode(), $e);
    }

    /**
     * Map HTTP exceptions
     */
    private function mapHttpException(\GuzzleHttp\Exception\RequestException $e): Throwable
    {
        $statusCode = $e->getResponse()?->getStatusCode() ?? 0;
        $responseBody = $e->getResponse()?->getBody()?->getContents();

        // Try to parse OpenAI error response
        if ($responseBody) {
            $errorData = json_decode($responseBody, true);

            if (isset($errorData['error'])) {
                $openAIError = $errorData['error'];
                $errorType = $openAIError['type'] ?? 'unknown_error';
                $errorMessage = $openAIError['message'] ?? $e->getMessage();
                $errorCode = $openAIError['code'] ?? null;

                return $this->mapOpenAIError($errorType, $errorMessage, $errorCode, $statusCode, $e);
            }
        }

        // Fallback to generic HTTP error mapping
        return $this->mapGenericHttpError($statusCode, $e->getMessage(), $e);
    }

    /**
     * Map OpenAI-specific errors by type
     */
    private function mapOpenAIError(string $type, string $message, ?string $code, int $statusCode, Throwable $original): Throwable
    {
        switch ($type) {
            case 'invalid_request_error':
                return $this->mapInvalidRequestError($message, $code, $statusCode, $original);

            case 'authentication_error':
                return new \DomainException("OpenAI authentication error: {$message}", 401, $original);

            case 'permission_error':
                return new \DomainException("OpenAI permission error: {$message}", 403, $original);

            case 'not_found_error':
                return new \DomainException("OpenAI resource not found: {$message}", 404, $original);

            case 'rate_limit_error':
                return $this->mapRateLimitError($original, $message);

            case 'api_error':
                return new \RuntimeException("OpenAI API error: {$message}", $statusCode, $original);

            case 'internal_error':
                return new \RuntimeException("OpenAI internal server error: {$message}", 500, $original);

            case 'service_unavailable_error':
                return new \RuntimeException("OpenAI service temporarily unavailable: {$message}", 503, $original);

            default:
                return new \DomainException("OpenAI API error ({$type}): {$message}", $statusCode, $original);
        }
    }

    /**
     * Map invalid request errors
     */
    private function mapInvalidRequestError(string $message, ?string $code, int $statusCode, Throwable $original): Throwable
    {
        // Common OpenAI invalid request error codes
        switch ($code) {
            case 'invalid_api_key':
                return new \DomainException("Invalid OpenAI API key: {$message}", 401, $original);

            case 'incorrect_api_key_provided':
                return new \DomainException("Incorrect OpenAI API key provided: {$message}", 401, $original);

            case 'model_not_found':
                return new \DomainException("OpenAI model not found: {$message}", 404, $original);

            case 'invalid_model':
                return new \InvalidArgumentException("Invalid OpenAI model: {$message}", $statusCode, $original);

            case 'max_tokens_exceeded':
                return new \InvalidArgumentException("Maximum tokens exceeded: {$message}", $statusCode, $original);

            case 'content_policy_violation':
                return new \DomainException("Content policy violation: {$message}", 400, $original);

            case 'billing_not_active':
                return new \DomainException("OpenAI billing not active: {$message}", 402, $original);

            case 'insufficient_quota':
                return new \DomainException("OpenAI quota exceeded: {$message}", 429, $original);

            default:
                return new \InvalidArgumentException("OpenAI invalid request: {$message}", $statusCode, $original);
        }
    }

    /**
     * Map rate limit errors
     */
    private function mapRateLimitError(\GuzzleHttp\Exception\RequestException $e, ?string $message = null): Throwable
    {
        $responseBody = $e->getResponse()?->getBody()?->getContents();

        if ($responseBody) {
            $errorData = json_decode($responseBody, true);

            if (isset($errorData['error'])) {
                $openAIError = $errorData['error'];
                $errorMessage = $openAIError['message'] ?? $message ?? $e->getMessage();
                $errorCode = $openAIError['code'] ?? null;

                // Handle specific rate limit scenarios
                if ($errorCode === 'rate_limit_exceeded') {
                    return new \RuntimeException("OpenAI rate limit exceeded: {$errorMessage}. Please retry after the specified time.", 429, $e);
                }

                if ($errorCode === 'insufficient_quota') {
                    return new \RuntimeException("OpenAI quota exceeded: {$errorMessage}. Please check your billing and usage limits.", 429, $e);
                }

                if ($errorCode === 'requests_per_minute_limit_exceeded') {
                    return new \RuntimeException("OpenAI requests per minute limit exceeded: {$errorMessage}", 429, $e);
                }

                if ($errorCode === 'tokens_per_minute_limit_exceeded') {
                    return new \RuntimeException("OpenAI tokens per minute limit exceeded: {$errorMessage}", 429, $e);
                }
            }
        }

        $defaultMessage = $message ?? 'OpenAI rate limit exceeded. Please retry after the specified time.';

        return new \RuntimeException($defaultMessage, 429, $e);
    }

    /**
     * Map generic HTTP errors
     */
    private function mapGenericHttpError(int $statusCode, string $message, Throwable $original): Throwable
    {
        switch ($statusCode) {
            case 400:
                return new \InvalidArgumentException("Bad request to OpenAI API: {$message}", $statusCode, $original);

            case 401:
                return new \DomainException("OpenAI authentication failed: {$message}", $statusCode, $original);

            case 402:
                return new \DomainException("OpenAI payment required: {$message}", $statusCode, $original);

            case 403:
                return new \DomainException("OpenAI access forbidden: {$message}", $statusCode, $original);

            case 404:
                return new \DomainException("OpenAI resource not found: {$message}", $statusCode, $original);

            case 429:
                return new \RuntimeException("OpenAI rate limit exceeded: {$message}", $statusCode, $original);

            case 500:
                return new \RuntimeException("OpenAI server error: {$message}", $statusCode, $original);

            case 502:
                return new \RuntimeException("OpenAI bad gateway: {$message}", $statusCode, $original);

            case 503:
                return new \RuntimeException("OpenAI service unavailable: {$message}", $statusCode, $original);

            case 504:
                return new \RuntimeException("OpenAI gateway timeout: {$message}", $statusCode, $original);

            default:
                return new \RuntimeException("OpenAI HTTP error ({$statusCode}): {$message}", $statusCode, $original);
        }
    }
}
