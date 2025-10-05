<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\JSONPlaceholder\API\Shared\Support;

use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Exceptions\DomainException;
use Plenipotentiary\Laravel\Exceptions\DomainInvalidException;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\ServerException;
use Throwable;

/**
 * Maps JSONPlaceholder/Saloon exceptions to domain exceptions.
 */
final class JSONPlaceholderErrorMapper implements ErrorMapperContract
{
    public function map(Throwable $e): Throwable
    {
        if ($e instanceof DomainException) {
            return $e; // already mapped upstream
        }

        if ($e instanceof ClientException) {
            return $this->mapClientException($e);
        }

        if ($e instanceof ServerException) {
            return $this->mapServerException($e);
        }

        if ($e instanceof RequestException) {
            return $this->mapRequestException($e);
        }

        return new DomainException(
            'ProviderError',
            $e->getMessage() ?: 'JSONPlaceholder request failed.',
            500,
            false,
            ['provider' => 'jsonplaceholder'],
            $e
        );
    }

    private function mapClientException(ClientException $exception): Throwable
    {
        $status = $exception->getStatus();
        $meta = [
            'provider' => 'jsonplaceholder',
            'status' => $status,
        ];

        return match ($status) {
            400 => new DomainInvalidException(
                [[
                    'field' => null,
                    'message' => $exception->getMessage() ?: 'Invalid request to JSONPlaceholder.',
                ]],
                $meta,
                $exception
            ),
            404 => new DomainException(
                'NotFound',
                $exception->getMessage() ?: 'Resource not found on JSONPlaceholder.',
                404,
                false,
                $meta,
                $exception
            ),
            401, 403 => new DomainException(
                'PermissionDenied',
                $exception->getMessage() ?: 'Access denied by JSONPlaceholder.',
                $status,
                false,
                $meta,
                $exception
            ),
            422 => new DomainInvalidException(
                [[
                    'field' => null,
                    'message' => $exception->getMessage() ?: 'Validation failed on JSONPlaceholder.',
                ]],
                $meta,
                $exception
            ),
            429 => new DomainException(
                'RateLimited',
                $exception->getMessage() ?: 'Rate limit exceeded on JSONPlaceholder.',
                429,
                true,
                $meta,
                $exception
            ),
            default => new DomainException(
                'ClientError',
                $exception->getMessage() ?: 'Client error on JSONPlaceholder.',
                $status,
                false,
                $meta,
                $exception
            ),
        };
    }

    private function mapServerException(ServerException $exception): Throwable
    {
        $status = $exception->getStatus();
        $meta = [
            'provider' => 'jsonplaceholder',
            'status' => $status,
        ];

        return new DomainException(
            'ProviderInternal',
            $exception->getMessage() ?: 'JSONPlaceholder service error.',
            $status,
            true, // Server errors are retryable
            $meta,
            $exception
        );
    }

    private function mapRequestException(RequestException $exception): Throwable
    {
        $meta = ['provider' => 'jsonplaceholder'];

        return new DomainException(
            'TransportError',
            $exception->getMessage() ?: 'Failed to connect to JSONPlaceholder.',
            0,
            true, // Network errors are retryable
            $meta,
            $exception
        );
    }
}
