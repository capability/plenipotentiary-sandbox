<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Api\Contexts\Default\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Example Saloon Request for creating chat completions with OpenAI API.
 * 
 * This demonstrates the REST/Saloon pattern where each API endpoint
 * is represented by its own self-contained Request class.
 */
final class CreateChatCompletionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $model,
        private readonly array $messages,
        private readonly ?float $temperature = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/chat/completions';
    }

    protected function defaultBody(): array
    {
        $body = [
            'model' => $this->model,
            'messages' => $this->messages,
        ];

        if ($this->temperature !== null) {
            $body['temperature'] = $this->temperature;
        }

        return $body;
    }
}
