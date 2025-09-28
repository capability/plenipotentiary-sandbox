<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Shared\Auth;

use GuzzleHttp\Client as GuzzleClient;
use Plenipotentiary\Laravel\Contracts\Client\ProviderClientContract;

/**
 * OpenAI SDK Client Wrapper
 *
 * Provides a unified interface to OpenAI API services.
 * Uses Guzzle HTTP client for direct API communication.
 */
final class OpenAISdkClient implements ProviderClientContract
{
    private GuzzleClient $httpClient;
    private OpenAIClientConfig $config;

    public function __construct(OpenAIClientConfig $config)
    {
        $this->config = $config;
        $this->httpClient = new GuzzleClient([
            'base_uri' => $this->config->getBaseUrl(),
            'timeout' => 60, // OpenAI can take longer for completions
            'headers' => $this->config->getHeaders(),
        ]);
    }

    /**
     * Get the raw HTTP client for direct API calls
     */
    public function request(string $method, string $endpoint, array $options = []): \Psr\Http\Message\ResponseInterface
    {
        $requestOptions = $this->prepareRequestOptions($options);
        
        return $this->httpClient->request($method, $endpoint, $requestOptions);
    }

    /**
     * Get the raw HTTP client
     */
    public function getHttpClient(): GuzzleClient
    {
        return $this->httpClient;
    }

    /**
     * Get the OpenAI client configuration
     */
    public function getConfiguration(): OpenAIClientConfig
    {
        return $this->config;
    }

    /**
     * Create a text completion
     */
    public function createCompletion(array $params): array
    {
        $response = $this->request('POST', '/completions', [
            'json' => $params
        ]);

        return $response->json();
    }

    /**
     * Create a chat completion
     */
    public function createChatCompletion(array $params): array
    {
        $response = $this->request('POST', '/chat/completions', [
            'json' => $params
        ]);

        return $response->json();
    }

    /**
     * Create an edit
     */
    public function createEdit(array $params): array
    {
        $response = $this->request('POST', '/edits', [
            'json' => $params
        ]);

        return $response->json();
    }

    /**
     * Create an image
     */
    public function createImage(array $params): array
    {
        $response = $this->request('POST', '/images/generations', [
            'json' => $params
        ]);

        return $response->json();
    }

    /**
     * Create an image edit
     */
    public function createImageEdit(array $params): array
    {
        $response = $this->request('POST', '/images/edits', [
            'multipart' => $this->buildMultipartData($params)
        ]);

        return $response->json();
    }

    /**
     * Create an image variation
     */
    public function createImageVariation(array $params): array
    {
        $response = $this->request('POST', '/images/variations', [
            'multipart' => $this->buildMultipartData($params)
        ]);

        return $response->json();
    }

    /**
     * Create embeddings
     */
    public function createEmbedding(array $params): array
    {
        $response = $this->request('POST', '/embeddings', [
            'json' => $params
        ]);

        return $response->json();
    }

    /**
     * Create moderation
     */
    public function createModeration(array $params): array
    {
        $response = $this->request('POST', '/moderations', [
            'json' => $params
        ]);

        return $response->json();
    }

    /**
     * List models
     */
    public function listModels(): array
    {
        $response = $this->request('GET', '/models');
        return $response->json();
    }

    /**
     * Retrieve a model
     */
    public function retrieveModel(string $modelId): array
    {
        $response = $this->request('GET', "/models/{$modelId}");
        return $response->json();
    }

    /**
     * Delete a model (for fine-tuned models)
     */
    public function deleteModel(string $modelId): array
    {
        $response = $this->request('DELETE', "/models/{$modelId}");
        return $response->json();
    }

    /**
     * Create a fine-tune
     */
    public function createFineTune(array $params): array
    {
        $response = $this->request('POST', '/fine-tunes', [
            'json' => $params
        ]);

        return $response->json();
    }

    /**
     * List fine-tunes
     */
    public function listFineTunes(): array
    {
        $response = $this->request('GET', '/fine-tunes');
        return $response->json();
    }

    /**
     * Retrieve a fine-tune
     */
    public function retrieveFineTune(string $fineTuneId): array
    {
        $response = $this->request('GET', "/fine-tunes/{$fineTuneId}");
        return $response->json();
    }

    /**
     * Cancel a fine-tune
     */
    public function cancelFineTune(string $fineTuneId): array
    {
        $response = $this->request('POST', "/fine-tunes/{$fineTuneId}/cancel");
        return $response->json();
    }

    /**
     * List fine-tune events
     */
    public function listFineTuneEvents(string $fineTuneId): array
    {
        $response = $this->request('GET', "/fine-tunes/{$fineTuneId}/events");
        return $response->json();
    }

    /**
     * Prepare request options for HTTP client
     */
    private function prepareRequestOptions(array $options): array
    {
        $requestOptions = [];

        // Handle headers (merge with existing auth headers)
        if (isset($options['headers'])) {
            $requestOptions['headers'] = array_merge(
                $this->config->getHeaders(),
                $options['headers']
            );
        }

        // Handle JSON body
        if (isset($options['json'])) {
            $requestOptions['json'] = $options['json'];
        }

        // Handle query parameters
        if (isset($options['query'])) {
            $requestOptions['query'] = $options['query'];
        }

        // Handle form data
        if (isset($options['form_params'])) {
            $requestOptions['form_params'] = $options['form_params'];
        }

        // Handle multipart data (for file uploads)
        if (isset($options['multipart'])) {
            $requestOptions['multipart'] = $options['multipart'];
        }

        // Handle timeout
        if (isset($options['timeout'])) {
            $requestOptions['timeout'] = $options['timeout'];
        }

        // Handle streaming responses
        if (isset($options['stream'])) {
            $requestOptions['stream'] = $options['stream'];
        }

        return $requestOptions;
    }

    /**
     * Build multipart data for file uploads (images)
     */
    private function buildMultipartData(array $params): array
    {
        $multipart = [];

        foreach ($params as $key => $value) {
            if ($key === 'image' && is_string($value) && file_exists($value)) {
                // Handle file upload
                $multipart[] = [
                    'name' => $key,
                    'contents' => fopen($value, 'r'),
                    'filename' => basename($value),
                ];
            } elseif ($key === 'mask' && is_string($value) && file_exists($value)) {
                // Handle mask file upload
                $multipart[] = [
                    'name' => $key,
                    'contents' => fopen($value, 'r'),
                    'filename' => basename($value),
                ];
            } else {
                // Handle regular form fields
                $multipart[] = [
                    'name' => $key,
                    'contents' => (string) $value,
                ];
            }
        }

        return $multipart;
    }
}
