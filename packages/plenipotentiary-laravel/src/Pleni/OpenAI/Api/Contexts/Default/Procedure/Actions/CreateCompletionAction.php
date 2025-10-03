<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Api\Contexts\Default\RpcConnector\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Plenipotentiary\Laravel\Contracts\Gateway\ApiRpcGatewayContract;
use Plenipotentiary\Laravel\Support\Result;

/**
 * Create Completion Action
 *
 * Business operation for creating OpenAI completions.
 */
class CreateCompletionAction
{
    use AsAction;

    public function __construct(
        private ApiRpcGatewayContract $gateway,
    ) {}

    public function handle(
        string $prompt,
        string $model = 'text-davinci-003',
        array $options = []
    ): Result {
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'max_tokens' => $options['max_tokens'] ?? 150,
            'temperature' => $options['temperature'] ?? 0.7,
            'top_p' => $options['top_p'] ?? 1,
            'frequency_penalty' => $options['frequency_penalty'] ?? 0,
            'presence_penalty' => $options['presence_penalty'] ?? 0,
        ];

        // Add optional parameters
        if (! empty($options['stop'])) {
            $payload['stop'] = $options['stop'];
        }

        if (! empty($options['suffix'])) {
            $payload['suffix'] = $options['suffix'];
        }

        if (! empty($options['echo'])) {
            $payload['echo'] = $options['echo'];
        }

        if (! empty($options['best_of'])) {
            $payload['best_of'] = $options['best_of'];
        }

        if (! empty($options['user'])) {
            $payload['user'] = $options['user'];
        }

        return $this->gateway->call('createCompletion', $payload);
    }
}
