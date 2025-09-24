<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Support;

final class Result implements \JsonSerializable
{
    private function __construct(
        private readonly string $kind,          // ok|err|invalid
        private readonly mixed $payload = null, // any
    ) {}

    public static function ok(mixed $value = null): self
    {
        return new self('ok', $value);
    }

    /** Accepts \Throwable|string|array */
    public static function err(mixed $error): self
    {
        if ($error instanceof \Throwable) {
            $error = [
                'error'   => 'Exception',
                'class'   => $error::class,
                'message' => $error->getMessage(),
            ];
        } elseif (is_string($error)) {
            $error = ['error' => $error];
        }
        return new self('err', $error);
    }

    /** $violations = [ ['field'=>'name','rule'=>'required', ...], ... ] */
    public static function invalid(array $violations): self
    {
        return new self('invalid', ['violations' => array_values($violations)]);
    }

    public function isOk(): bool      { return $this->kind === 'ok'; }
    public function isErr(): bool     { return $this->kind === 'err'; }
    public function isInvalid(): bool { return $this->kind === 'invalid'; }

    /** Unwrap ok value, throws if not ok */
    public function unwrap(): mixed
    {
        if (!$this->isOk()) {
            throw new \LogicException('Attempted to unwrap a non-ok Result');
        }
        return $this->payload;
    }

    /** Returns normalised error payload for err, null otherwise */
    public function error(): ?array
    {
        return $this->isErr() ? (array) $this->payload : null;
    }

    /** Returns violations list for invalid, null otherwise */
    public function violations(): ?array
    {
        return $this->isInvalid() ? (array) ($this->payload['violations'] ?? null) : null;
    }

    /** Map ok value, passthrough otherwise */
    public function map(callable $fn): self
    {
        return $this->isOk() ? self::ok($fn($this->payload)) : $this;
    }

    /** Map error payload when err|invalid, passthrough ok */
    public function mapError(callable $fn): self
    {
        if ($this->isErr())     return self::err($fn($this->payload));
        if ($this->isInvalid()) return self::invalid($fn($this->payload['violations'] ?? []));
        return $this;
    }

    public function toArray(): array
    {
        return ['kind' => $this->kind, 'payload' => $this->payload];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
