<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support;

use Plenipotentiary\Laravel\Support\MappedError;
use Throwable;

/**
 * eBay Browse API Error Mapper
 *
 * Maps eBay-specific exceptions into domain-friendly errors.
 * Handles various eBay API error scenarios and provides meaningful error messages.
 */
final class eBayErrorMapper
{
    /**
     * Map provider-specific exceptions into domain-friendly errors
     */
    public function map(Throwable $e): MappedError
    {
        // Handle Saloon HTTP exceptions
        if ($e instanceof \Saloon\Exceptions\Request\RequestException) {
            return $this->mapSaloonException($e);
        }

        // Handle Guzzle HTTP exceptions
        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
            return $this->mapHttpException($e);
        }

        // Handle authentication errors
        if ($e instanceof \GuzzleHttp\Exception\ClientException) {
            $statusCode = $e->getResponse()?->getStatusCode();

            if ($statusCode === 401) {
                return new MappedError(
                    code: 'AUTHENTICATION_ERROR',
                    message: 'eBay authentication failed. Please check your credentials.',
                    httpStatus: 401,
                    retryable: false,
                    previous: $e
                );
            }

            if ($statusCode === 403) {
                return new MappedError(
                    code: 'AUTHORIZATION_ERROR',
                    message: 'eBay access forbidden. Check your API permissions and scopes.',
                    httpStatus: 403,
                    retryable: false,
                    previous: $e
                );
            }
        }

        // Handle rate limiting
        if ($e instanceof \GuzzleHttp\Exception\ServerException) {
            $statusCode = $e->getResponse()?->getStatusCode();

            if ($statusCode === 429) {
                return new MappedError(
                    code: 'RATE_LIMIT_EXCEEDED',
                    message: 'eBay API rate limit exceeded. Please retry after the specified time.',
                    httpStatus: 429,
                    retryable: true,
                    previous: $e
                );
            }
        }

        // For other exceptions, wrap them as domain exceptions
        return new MappedError(
            code: 'UNKNOWN_ERROR',
            message: "eBay API error: {$e->getMessage()}",
            httpStatus: $e->getCode() ?: 500,
            retryable: false,
            previous: $e
        );
    }

    /**
     * Map Saloon request exceptions
     */
    private function mapSaloonException(\Saloon\Exceptions\Request\RequestException $e): MappedError
    {
        $response = $e->getResponse();
        $statusCode = $response->status();
        $body = $response->json();

        // Try to parse eBay error response
        if (isset($body['errors']) && is_array($body['errors'])) {
            $error = $body['errors'][0] ?? [];
            $errorId = $error['errorId'] ?? 'UNKNOWN_ERROR';
            $errorMessage = $error['message'] ?? $e->getMessage();
            $errorCategory = $error['category'] ?? 'REQUEST_ERROR';

            return $this->mapEbayErrorByCategory($errorCategory, $errorId, $errorMessage, $statusCode, $e);
        }

        // Map by HTTP status code
        return $this->mapByHttpStatus($statusCode, $e->getMessage(), $e);
    }

    /**
     * Map eBay errors by category
     */
    private function mapEbayErrorByCategory(
        string $category,
        string $errorId,
        string $message,
        int $statusCode,
        Throwable $original
    ): MappedError {
        $retryable = $this->isRetryable($category, $errorId, $statusCode);

        return match ($category) {
            'REQUEST_ERROR' => new MappedError(
                code: $errorId,
                message: "Invalid request: {$message}",
                httpStatus: $statusCode,
                retryable: false,
                previous: $original
            ),
            'SYSTEM_ERROR' => new MappedError(
                code: $errorId,
                message: "eBay system error: {$message}",
                httpStatus: $statusCode,
                retryable: true,
                previous: $original
            ),
            'APPLICATION_ERROR' => new MappedError(
                code: $errorId,
                message: "eBay application error: {$message}",
                httpStatus: $statusCode,
                retryable: $retryable,
                previous: $original
            ),
            'AUTHENTICATION_ERROR' => new MappedError(
                code: 'AUTHENTICATION_ERROR',
                message: "Authentication error: {$message}",
                httpStatus: 401,
                retryable: false,
                previous: $original
            ),
            'AUTHORIZATION_ERROR' => new MappedError(
                code: 'AUTHORIZATION_ERROR',
                message: "Authorization error: {$message}",
                httpStatus: 403,
                retryable: false,
                previous: $original
            ),
            default => new MappedError(
                code: $errorId,
                message: "eBay error ({$category}): {$message}",
                httpStatus: $statusCode,
                retryable: $retryable,
                previous: $original
            ),
        };
    }

    /**
     * Map by HTTP status code
     */
    private function mapByHttpStatus(int $statusCode, string $message, Throwable $original): MappedError
    {
        return match ($statusCode) {
            400 => new MappedError(
                code: 'BAD_REQUEST',
                message: "Bad request: {$message}",
                httpStatus: $statusCode,
                retryable: false,
                previous: $original
            ),
            401 => new MappedError(
                code: 'AUTHENTICATION_ERROR',
                message: "Authentication failed: {$message}",
                httpStatus: $statusCode,
                retryable: false,
                previous: $original
            ),
            403 => new MappedError(
                code: 'AUTHORIZATION_ERROR',
                message: "Access forbidden: {$message}",
                httpStatus: $statusCode,
                retryable: false,
                previous: $original
            ),
            404 => new MappedError(
                code: 'NOT_FOUND',
                message: "Resource not found: {$message}",
                httpStatus: $statusCode,
                retryable: false,
                previous: $original
            ),
            429 => new MappedError(
                code: 'RATE_LIMIT_EXCEEDED',
                message: "Rate limit exceeded: {$message}",
                httpStatus: $statusCode,
                retryable: true,
                previous: $original
            ),
            500, 502, 503, 504 => new MappedError(
                code: 'SERVER_ERROR',
                message: "Server error: {$message}",
                httpStatus: $statusCode,
                retryable: true,
                previous: $original
            ),
            default => new MappedError(
                code: 'HTTP_ERROR',
                message: "HTTP error ({$statusCode}): {$message}",
                httpStatus: $statusCode,
                retryable: $statusCode >= 500,
                previous: $original
            ),
        };
    }

    /**
     * Map HTTP exceptions
     */
    private function mapHttpException(\GuzzleHttp\Exception\RequestException $e): MappedError
    {
        $statusCode = $e->getResponse()?->getStatusCode() ?? 0;
        return $this->mapByHttpStatus($statusCode, $e->getMessage(), $e);
    }

    /**
     * Determine if an error is retryable
     */
    private function isRetryable(string $category, string $errorId, int $statusCode): bool
    {
        // System errors are usually retryable
        if ($category === 'SYSTEM_ERROR') {
            return true;
        }

        // Rate limits are retryable
        if ($errorId === 'RATE_LIMIT_EXCEEDED' || $errorId === 'QUOTA_EXCEEDED') {
            return true;
        }

        // 5xx errors are retryable
        if ($statusCode >= 500) {
            return true;
        }

        // Everything else is not retryable by default
        return false;
    }
}

