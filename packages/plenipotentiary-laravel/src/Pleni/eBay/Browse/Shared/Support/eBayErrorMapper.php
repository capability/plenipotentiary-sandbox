<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support;

use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Throwable;

/**
 * eBay Browse API Error Mapper
 *
 * Maps eBay-specific exceptions into domain-friendly errors.
 * Handles various eBay API error scenarios and provides meaningful error messages.
 */
final class eBayErrorMapper implements ErrorMapperContract
{
    /**
     * Map provider-specific exceptions into your app's domain-friendly errors
     */
    public function map(Throwable $e): Throwable
    {
        // Handle eBay SDK specific exceptions
        if ($e instanceof \Bricre\EbaySdkBuyBrowse\ApiException) {
            return $this->mapEbayApiException($e);
        }

        // Handle Guzzle HTTP exceptions
        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
            return $this->mapHttpException($e);
        }

        // Handle authentication errors
        if ($e instanceof \GuzzleHttp\Exception\ClientException) {
            $statusCode = $e->getResponse()?->getStatusCode();
            
            if ($statusCode === 401) {
                return new \DomainException('eBay authentication failed. Please check your credentials.', 401, $e);
            }
            
            if ($statusCode === 403) {
                return new \DomainException('eBay access forbidden. Check your API permissions and scopes.', 403, $e);
            }
        }

        // Handle rate limiting
        if ($e instanceof \GuzzleHttp\Exception\ServerException) {
            $statusCode = $e->getResponse()?->getStatusCode();
            
            if ($statusCode === 429) {
                return new \DomainException('eBay API rate limit exceeded. Please retry after the specified time.', 429, $e);
            }
        }

        // For other exceptions, wrap them as domain exceptions
        return new \DomainException("eBay API error: {$e->getMessage()}", $e->getCode(), $e);
    }

    /**
     * Map eBay SDK API exceptions
     */
    private function mapEbayApiException(\Bricre\EbaySdkBuyBrowse\ApiException $e): Throwable
    {
        $responseBody = $e->getResponseBody();
        $statusCode = $e->getCode();
        
        // Try to parse eBay error response
        if ($responseBody) {
            $errorData = json_decode($responseBody, true);
            
            if (isset($errorData['errors'])) {
                $error = $errorData['errors'][0] ?? [];
                $errorId = $error['errorId'] ?? 'UNKNOWN_ERROR';
                $errorMessage = $error['message'] ?? $e->getMessage();
                $errorCategory = $error['category'] ?? 'REQUEST_ERROR';
                
                return $this->mapEbayErrorByCategory($errorCategory, $errorId, $errorMessage, $statusCode, $e);
            }
        }

        // Fallback to generic eBay error
        return new \DomainException("eBay API error (HTTP {$statusCode}): {$e->getMessage()}", $statusCode, $e);
    }

    /**
     * Map eBay errors by category
     */
    private function mapEbayErrorByCategory(string $category, string $errorId, string $message, int $statusCode, Throwable $original): Throwable
    {
        switch ($category) {
            case 'REQUEST_ERROR':
                return $this->mapRequestError($errorId, $message, $statusCode, $original);
                
            case 'SYSTEM_ERROR':
                return $this->mapSystemError($errorId, $message, $statusCode, $original);
                
            case 'APPLICATION_ERROR':
                return $this->mapApplicationError($errorId, $message, $statusCode, $original);
                
            case 'AUTHENTICATION_ERROR':
                return new \DomainException("eBay authentication error: {$message}", 401, $original);
                
            case 'AUTHORIZATION_ERROR':
                return new \DomainException("eBay authorization error: {$message}", 403, $original);
                
            default:
                return new \DomainException("eBay API error ({$category}): {$message}", $statusCode, $original);
        }
    }

    /**
     * Map request errors
     */
    private function mapRequestError(string $errorId, string $message, int $statusCode, Throwable $original): Throwable
    {
        switch ($errorId) {
            case 'INVALID_REQUEST':
                return new \InvalidArgumentException("Invalid eBay request: {$message}", $statusCode, $original);
                
            case 'MISSING_FIELD':
                return new \InvalidArgumentException("Missing required field in eBay request: {$message}", $statusCode, $original);
                
            case 'INVALID_FIELD_VALUE':
                return new \InvalidArgumentException("Invalid field value in eBay request: {$message}", $statusCode, $original);
                
            case 'INVALID_PARAMETER':
                return new \InvalidArgumentException("Invalid parameter in eBay request: {$message}", $statusCode, $original);
                
            default:
                return new \DomainException("eBay request error: {$message}", $statusCode, $original);
        }
    }

    /**
     * Map system errors
     */
    private function mapSystemError(string $errorId, string $message, int $statusCode, Throwable $original): Throwable
    {
        switch ($errorId) {
            case 'SERVICE_UNAVAILABLE':
                return new \RuntimeException("eBay service temporarily unavailable: {$message}", 503, $original);
                
            case 'INTERNAL_SERVER_ERROR':
                return new \RuntimeException("eBay internal server error: {$message}", 500, $original);
                
            case 'SERVICE_TIMEOUT':
                return new \RuntimeException("eBay service timeout: {$message}", 504, $original);
                
            default:
                return new \RuntimeException("eBay system error: {$message}", $statusCode, $original);
        }
    }

    /**
     * Map application errors
     */
    private function mapApplicationError(string $errorId, string $message, int $statusCode, Throwable $original): Throwable
    {
        switch ($errorId) {
            case 'RATE_LIMIT_EXCEEDED':
                return new \RuntimeException("eBay rate limit exceeded: {$message}", 429, $original);
                
            case 'QUOTA_EXCEEDED':
                return new \RuntimeException("eBay quota exceeded: {$message}", 429, $original);
                
            case 'ITEM_NOT_FOUND':
                return new \DomainException("eBay item not found: {$message}", 404, $original);
                
            case 'INVALID_ITEM_ID':
                return new \InvalidArgumentException("Invalid eBay item ID: {$message}", $statusCode, $original);
                
            default:
                return new \DomainException("eBay application error: {$message}", $statusCode, $original);
        }
    }

    /**
     * Map HTTP exceptions
     */
    private function mapHttpException(\GuzzleHttp\Exception\RequestException $e): Throwable
    {
        $statusCode = $e->getResponse()?->getStatusCode() ?? 0;
        
        switch ($statusCode) {
            case 400:
                return new \InvalidArgumentException("Bad request to eBay API: {$e->getMessage()}", $statusCode, $e);
                
            case 401:
                return new \DomainException("eBay authentication failed: {$e->getMessage()}", $statusCode, $e);
                
            case 403:
                return new \DomainException("eBay access forbidden: {$e->getMessage()}", $statusCode, $e);
                
            case 404:
                return new \DomainException("eBay resource not found: {$e->getMessage()}", $statusCode, $e);
                
            case 429:
                return new \RuntimeException("eBay rate limit exceeded: {$e->getMessage()}", $statusCode, $e);
                
            case 500:
                return new \RuntimeException("eBay server error: {$e->getMessage()}", $statusCode, $e);
                
            case 503:
                return new \RuntimeException("eBay service unavailable: {$e->getMessage()}", $statusCode, $e);
                
            default:
                return new \RuntimeException("eBay HTTP error ({$statusCode}): {$e->getMessage()}", $statusCode, $e);
        }
    }
}
