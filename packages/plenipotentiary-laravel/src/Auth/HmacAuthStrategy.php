<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Auth;

use Plenipotentiary\Laravel\Contracts\Auth\AuthStrategyContract;
use Psr\Http\Message\RequestInterface;

/**
 * HMAC authentication strategy for HTTP-based APIs.
 *
 * Builds an Authorization header of the form:
 *   <prefix><keyId>:<base64(hmac(secret, stringToSign))>
 *
 * String to sign is composed of the lowercase HTTP method, request path,
 * and a set of signed headers such as `(request-target)`, `date`, and `content-digest`.
 *
 * This allows stateless request signing and verification by the remote server.
 */
final class HmacAuthStrategy implements AuthStrategyContract
{
    /**
     * @param string   $keyId         Public key identifier/client ID
     * @param string   $secret        Shared secret for HMAC signing
     * @param string   $algo          Hash algorithm, default 'sha256'
     * @param string   $header        Header name to set, default 'Authorization'
     * @param string   $prefix        Prefix before auth value, default 'HMAC '
     * @param string[] $signedHeaders List of headers included in the signature
     */
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

    /**
     * Build the HMAC signature string for a request.
     *
     * @param RequestInterface $req
     * @return string base64-encoded signature
     */
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
