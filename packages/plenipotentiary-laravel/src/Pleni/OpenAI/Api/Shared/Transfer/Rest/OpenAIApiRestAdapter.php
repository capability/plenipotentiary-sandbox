<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Api\Shared\Transfer\Rest;

use Plenipotentiary\Laravel\Contracts\Adapter\RestAdapterContract;
use Plenipotentiary\Laravel\Pleni\OpenAI\Api\Shared\Support\OpenAIErrorMapper;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Throwable;

/**
 * OpenAI API REST adapter using Saloon.
 *
 * This adapter handles communication with OpenAI REST APIs,
 * leveraging saloonphp/saloon for HTTP communication.
 */
final class OpenAIApiRestAdapter implements RestAdapterContract
{
    public function __construct(
        private OpenAIApiRestConnector $connector,
        private OpenAIErrorMapper $errorMapper,
        private LoggerInterface $logger,
    ) {}

    /**
     * Execute a Saloon request against OpenAI REST API.
     */
    public function execute(Request $request): Result
    {
        try {
            $this->logger->info('OpenAI API REST: Executing request', [
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
            $this->logger->error('OpenAI API REST: Request failed', [
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
            'message' => $body['error']['message'] ?? 'Unknown error',
            'httpStatus' => $response->status(),
            'retryable' => $response->status() >= 500,
            'details' => $body,
        ]);
    }
}
