<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Transfer\Rest;

use Plenipotentiary\Laravel\Contracts\Adapter\RestAdapterContract;
use Plenipotentiary\Laravel\Pleni\eBay\Browse\Shared\Support\eBayErrorMapper;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

/**
 * eBay Browse REST adapter using Saloon.
 * 
 * This adapter handles communication with eBay Browse REST APIs,
 * leveraging saloonphp/saloon for HTTP communication.
 */
final class eBayBrowseRestAdapter implements RestAdapterContract
{
    public function __construct(
        private eBayBrowseRestConnector $connector,
        private eBayErrorMapper $errorMapper,
        private LoggerInterface $logger,
    ) {}

    /**
     * Execute a Saloon request against eBay Browse REST API.
     */
    public function execute(Request $request): Result
    {
        try {
            $this->logger->info('eBay Browse REST: Executing request', [
                'request_class' => $request::class,
            ]);

            /** @var Response $response */
            $response = $this->connector->send($request);

            if ($response->successful()) {
                return Result::ok($response->json());
            }

            // Map HTTP error to domain error
            return $this->mapHttpError($response);
        } catch (Throwable $exception) {
            $this->logger->error('eBay Browse REST: Request failed', [
                'request_class' => $request::class,
                'exception' => $exception->getMessage(),
            ]);

            $mapped = $this->errorMapper->map($exception);
            
            return Result::err([
                'code' => $mapped->code(),
                'message' => $mapped->getMessage(),
                'httpStatus' => $mapped->httpStatus(),
                'retryable' => $mapped->isRetryable(),
            ]);
        }
    }

    private function mapHttpError(Response $response): Result
    {
        $body = $response->json();
        
        return Result::err([
            'code' => 'HTTP_ERROR',
            'message' => $body['errors'][0]['message'] ?? 'Unknown error',
            'httpStatus' => $response->status(),
            'retryable' => $response->status() >= 500,
            'details' => $body,
        ]);
    }
}
