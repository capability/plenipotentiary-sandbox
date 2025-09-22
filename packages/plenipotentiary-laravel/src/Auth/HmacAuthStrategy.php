<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\AuthStrategyContract;
use Psr\Http\Message\RequestInterface;

final class HmacAuthStrategy implements AuthStrategyContract
{
    public function __construct(
        private string $keyId,
        private string $secret,
        private string $algo = 'sha256',
        private string $header = 'Authorization',
        private string $prefix = 'HMAC ',
        private array $signedHeaders = ['(request-target)', 'date', 'content-digest'],
    ) {}

    public function apply(RequestInterface $request, array $context = []): RequestInterface
    {
        $signature = $this->sign($request);
        return $request->withHeader($this->header, $this->prefix.$this->keyId.':'.$signature);
    }

    private function sign(RequestInterface $req): string
    {
        $method = strtolower($req->getMethod());
        $path   = (string) $req->getUri()->withScheme('')->withHost('')->withPort(null);
        $lines  = [];

        foreach ($this->signedHeaders as $h) {
            if ($h === '(request-target)') {
                $lines[] = "(request-target): {$method} {$path}";
            } else {
                $lines[] = strtolower($h).': '.$req->getHeaderLine($h);
            }
        }

        $stringToSign = implode("\n", $lines);
        return base64_encode(hash_hmac($this->algo, $stringToSign, $this->secret, true));
    }
}
