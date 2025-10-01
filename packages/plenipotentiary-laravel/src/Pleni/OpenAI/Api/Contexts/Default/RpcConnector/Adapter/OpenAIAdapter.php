<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Api\Contexts\Default\RpcConnector\Adapter;

use Plenipotentiary\Laravel\Contracts\Adapter\ApiRpcAdapterContract;
use Plenipotentiary\Laravel\Contracts\Client\HttpProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Support\Result;
use Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Support\OpenAIConfig;
use Psr\Log\LoggerInterface;

final class OpenAIApiRpcAdapter implements ApiRpcAdapterContract
{
    public function __construct(
        private HttpProviderClientContract $client,
        private ErrorMapperContract $errorMapper,
        private LoggerInterface $logger,
    ) {}

    public function call(string $operation, array $payload = [], array $options = []): Result
    {
        try {
            $endpointConfig = $this->getEndpointConfig($operation);
            [$endpoint, $request] = $this->buildRequest($endpointConfig, $payload, $options);

            $this->logger->info('OpenAI API call', [
                'operation' => $operation,
                'method' => $endpointConfig['method'],
                'endpoint' => $endpoint,
            ]);

            $response = $this->client->request(
                $endpointConfig['method'],
                $endpoint,
                $request
            );

            $data = json_decode((string) $response->getBody(), true) ?? [];

            return Result::ok($data);

        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    public function validate(string $operation, array $payload = []): Result
    {
        try {
            $violations = $this->validatePayload($operation, $payload);

            return $violations ? Result::invalid($violations) : Result::ok();
        } catch (\Throwable $e) {
            return Result::err($this->errorMapper->map($e));
        }
    }

    private function getEndpointConfig(string $operation): array
    {
        return match ($operation) {
            'createCompletion' => ['method' => 'POST', 'endpoint' => '/v1/completions'],
            'createChatCompletion' => ['method' => 'POST', 'endpoint' => '/v1/chat/completions'],
            'createEdit' => ['method' => 'POST', 'endpoint' => '/v1/edits'],
            'createImage' => ['method' => 'POST', 'endpoint' => '/v1/images/generations'],
            'createImageEdit' => ['method' => 'POST', 'endpoint' => '/v1/images/edits'],
            'createImageVariation' => ['method' => 'POST', 'endpoint' => '/v1/images/variations'],
            'createEmbedding' => ['method' => 'POST', 'endpoint' => '/v1/embeddings'],
            'createModeration' => ['method' => 'POST', 'endpoint' => '/v1/moderations'],
            'listModels' => ['method' => 'GET', 'endpoint' => '/v1/models'],
            'retrieveModel' => ['method' => 'GET', 'endpoint' => '/v1/models/{modelId}'],
            'deleteModel' => ['method' => 'DELETE', 'endpoint' => '/v1/models/{modelId}'],
            'createFineTune' => ['method' => 'POST', 'endpoint' => '/v1/fine-tunes'],
            'listFineTunes' => ['method' => 'GET', 'endpoint' => '/v1/fine-tunes'],
            'retrieveFineTune' => ['method' => 'GET', 'endpoint' => '/v1/fine-tunes/{fineTuneId}'],
            'cancelFineTune' => ['method' => 'POST', 'endpoint' => '/v1/fine-tunes/{fineTuneId}/cancel'],
            'listFineTuneEvents' => ['method' => 'GET', 'endpoint' => '/v1/fine-tunes/{fineTuneId}/events'],
            default => throw new \InvalidArgumentException("Unknown operation: {$operation}")
        };
    }

    private function validatePayload(string $operation, array $payload): array
    {
        $violations = [];
        switch ($operation) {
            case 'createCompletion':
                if (empty($payload['model'])) {
                    $violations[] = ['field' => 'model', 'rule' => 'required', 'message' => 'Model is required'];
                }
                if (empty($payload['prompt'])) {
                    $violations[] = ['field' => 'prompt', 'rule' => 'required', 'message' => 'Prompt is required'];
                }
                break;
            case 'createChatCompletion':
                if (empty($payload['model'])) {
                    $violations[] = ['field' => 'model', 'rule' => 'required', 'message' => 'Model is required'];
                }
                if (empty($payload['messages']) || ! is_array($payload['messages'])) {
                    $violations[] = ['field' => 'messages', 'rule' => 'required|array', 'message' => 'Messages array is required'];
                }
                break;
            case 'createEmbedding':
                if (empty($payload['model'])) {
                    $violations[] = ['field' => 'model', 'rule' => 'required', 'message' => 'Model is required'];
                }
                if (empty($payload['input'])) {
                    $violations[] = ['field' => 'input', 'rule' => 'required', 'message' => 'Input is required'];
                }
                break;
            case 'createImage':
                if (empty($payload['prompt'])) {
                    $violations[] = ['field' => 'prompt', 'rule' => 'required', 'message' => 'Prompt is required for image generation'];
                }
                break;
        }

        return $violations;
    }

    /**
     * @return array{0:string,1:array}
     */
    private function buildRequest(array $config, array $payload, array $options): array
    {
        $headers = array_merge([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.($options['api_key'] ?? OpenAIConfig::apiKey()),
        ], $options['headers'] ?? []);

        $endpoint = $config['endpoint'];
        if (isset($options['modelId'])) {
            $endpoint = str_replace('{modelId}', $options['modelId'], $endpoint);
        }
        if (isset($options['fineTuneId'])) {
            $endpoint = str_replace('{fineTuneId}', $options['fineTuneId'], $endpoint);
        }

        $request = [
            'headers' => $headers,
            'json' => $payload,
        ];

        return [$endpoint, $request];
    }
}
