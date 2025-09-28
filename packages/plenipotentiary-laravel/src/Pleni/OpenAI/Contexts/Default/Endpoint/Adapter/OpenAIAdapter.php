<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\ApiEndpointAdapterContract;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Pleni\Support\Result;
use Psr\Log\LoggerInterface;

/**
 * OpenAI API Adapter
 * 
 * Provider-specific implementation for OpenAI API operations.
 * Handles API communication, request building, and response mapping.
 */
final class OpenAIAdapter implements ApiEndpointAdapterContract
{
    public function __construct(
        private ProviderClientContract $client,
        private ErrorMapperContract $errorMapper,
        private LoggerInterface $logger,
    ) {}

    public function call(string $operation, array $payload = [], array $options = []): Result
    {
        try {
            $endpointConfig = $this->getEndpointConfig($operation);
            
            $request = $this->buildRequest($endpointConfig, $payload, $options);
            
            $this->logger->info('OpenAI API call', [
                'operation' => $operation,
                'method' => $endpointConfig['method'],
                'endpoint' => $endpointConfig['endpoint'],
            ]);

            $response = $this->client->request(
                $endpointConfig['method'],
                $endpointConfig['endpoint'],
                $request
            );

            return Result::ok($response->json());

        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    public function validate(string $operation, array $payload = []): Result
    {
        try {
            $endpointConfig = $this->getEndpointConfig($operation);
            
            // OpenAI-specific validation logic
            $violations = $this->validatePayload($operation, $payload);
            if ($violations) {
                return Result::invalid($violations);
            }

            // OpenAI doesn't have a separate validation endpoint,
            // so we just return ok if validation passes
            return Result::ok();

        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    /**
     * Get endpoint configuration for an operation
     */
    private function getEndpointConfig(string $operation): array
    {
        return match ($operation) {
            'createCompletion' => [
                'method' => 'POST',
                'endpoint' => '/v1/completions'
            ],
            'createChatCompletion' => [
                'method' => 'POST',
                'endpoint' => '/v1/chat/completions'
            ],
            'createEdit' => [
                'method' => 'POST',
                'endpoint' => '/v1/edits'
            ],
            'createImage' => [
                'method' => 'POST',
                'endpoint' => '/v1/images/generations'
            ],
            'createImageEdit' => [
                'method' => 'POST',
                'endpoint' => '/v1/images/edits'
            ],
            'createImageVariation' => [
                'method' => 'POST',
                'endpoint' => '/v1/images/variations'
            ],
            'createEmbedding' => [
                'method' => 'POST',
                'endpoint' => '/v1/embeddings'
            ],
            'createModeration' => [
                'method' => 'POST',
                'endpoint' => '/v1/moderations'
            ],
            'listModels' => [
                'method' => 'GET',
                'endpoint' => '/v1/models'
            ],
            'retrieveModel' => [
                'method' => 'GET',
                'endpoint' => '/v1/models/{modelId}'
            ],
            'deleteModel' => [
                'method' => 'DELETE',
                'endpoint' => '/v1/models/{modelId}'
            ],
            'createFineTune' => [
                'method' => 'POST',
                'endpoint' => '/v1/fine-tunes'
            ],
            'listFineTunes' => [
                'method' => 'GET',
                'endpoint' => '/v1/fine-tunes'
            ],
            'retrieveFineTune' => [
                'method' => 'GET',
                'endpoint' => '/v1/fine-tunes/{fineTuneId}'
            ],
            'cancelFineTune' => [
                'method' => 'POST',
                'endpoint' => '/v1/fine-tunes/{fineTuneId}/cancel'
            ],
            'listFineTuneEvents' => [
                'method' => 'GET',
                'endpoint' => '/v1/fine-tunes/{fineTuneId}/events'
            ],
            default => throw new \InvalidArgumentException("Unknown operation: {$operation}")
        };
    }

    /**
     * Validate payload for specific operations
     */
    private function validatePayload(string $operation, array $payload): array
    {
        $violations = [];

        switch ($operation) {
            case 'createCompletion':
                if (empty($payload['model'])) {
                    $violations[] = [
                        'field' => 'model',
                        'rule' => 'required',
                        'message' => 'Model is required'
                    ];
                }
                if (empty($payload['prompt'])) {
                    $violations[] = [
                        'field' => 'prompt',
                        'rule' => 'required',
                        'message' => 'Prompt is required'
                    ];
                }
                break;
                
            case 'createChatCompletion':
                if (empty($payload['model'])) {
                    $violations[] = [
                        'field' => 'model',
                        'rule' => 'required',
                        'message' => 'Model is required'
                    ];
                }
                if (empty($payload['messages']) || !is_array($payload['messages'])) {
                    $violations[] = [
                        'field' => 'messages',
                        'rule' => 'required|array',
                        'message' => 'Messages array is required'
                    ];
                }
                break;
                
            case 'createEmbedding':
                if (empty($payload['model'])) {
                    $violations[] = [
                        'field' => 'model',
                        'rule' => 'required',
                        'message' => 'Model is required'
                    ];
                }
                if (empty($payload['input'])) {
                    $violations[] = [
                        'field' => 'input',
                        'rule' => 'required',
                        'message' => 'Input is required'
                    ];
                }
                break;
                
            case 'createImage':
                if (empty($payload['prompt'])) {
                    $violations[] = [
                        'field' => 'prompt',
                        'rule' => 'required',
                        'message' => 'Prompt is required for image generation'
                    ];
                }
                break;
                
            case 'retrieveModel':
            case 'deleteModel':
                // Model ID should be provided in options
                break;
                
            case 'createFineTune':
            case 'retrieveFineTune':
            case 'cancelFineTune':
            case 'listFineTuneEvents':
                // Fine-tune ID should be provided in options for some operations
                break;
        }

        return $violations;
    }

    /**
     * Build HTTP request from endpoint config, payload, and options
     */
    private function buildRequest(array $config, array $payload, array $options): array
    {
        $request = [
            'headers' => array_merge([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . ($options['api_key'] ?? env('OPENAI_API_KEY')),
            ], $options['headers'] ?? []),
        ];

        // Handle URL parameters
        $endpoint = $config['endpoint'];
        if (isset($options['modelId'])) {
            $endpoint = str_replace('{modelId}', $options['modelId'], $endpoint);
        }
        if (isset($options['fineTuneId'])) {
            $endpoint = str_replace('{fineTuneId}', $options['fineTuneId'], $endpoint);
        }

        // Set endpoint with parameters
        $request['endpoint'] = $endpoint;

        // OpenAI uses JSON for all requests
        $request['json'] = $payload;

        return $request;
    }
}
