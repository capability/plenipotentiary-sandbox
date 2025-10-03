<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\MCP\Shared\Support;

/**
 * Response from MCP server
 */
final class McpResponse
{
    public function __construct(
        public readonly mixed $content,
        public readonly bool $isError = false,
        public readonly ?string $errorMessage = null,
        public readonly ?string $errorCode = null,
        public readonly string $contentType = 'text/plain',
        public readonly array $meta = [],
    ) {}

    public static function success(mixed $content, string $contentType = 'text/plain', array $meta = []): self
    {
        return new self(
            content: $content,
            isError: false,
            contentType: $contentType,
            meta: $meta,
        );
    }

    public static function error(string $message, ?string $code = null, mixed $content = null): self
    {
        return new self(
            content: $content,
            isError: true,
            errorMessage: $message,
            errorCode: $code,
        );
    }
}
