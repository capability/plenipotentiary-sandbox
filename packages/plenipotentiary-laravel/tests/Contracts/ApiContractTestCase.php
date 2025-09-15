<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Tests\Contracts;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class ApiContractTestCase extends BaseTestCase
{
    protected string $examplesRoot = '';

    protected function setUp(): void
    {
        parent::setUp();

        $candidates = [
            getenv('EXAMPLES_ROOT') ?: null,
            realpath(\dirname(__DIR__, 4).'/docs/openapi/examples') ?: null, // repo root/docs/...
            realpath(getcwd().'/../../docs/openapi/examples') ?: null,       // when CWD = package/
        ];

        foreach ($candidates as $dir) {
            if ($dir && is_dir($dir)) {
                $this->examplesRoot = $dir;
                break;
            }
        }

        if ($this->examplesRoot === '') {
            $this->fail('EXAMPLES_ROOT not found. Set env EXAMPLES_ROOT or add docs/openapi/examples to repo.');
        }

        Http::preventStrayRequests();
    }

    protected function example(string $relative): array
    {
        $path = rtrim($this->examplesRoot, '/').'/'.ltrim($relative, '/');
        if (! is_file($path)) {
            $this->fail("Missing example fixture: {$path}");
        }
        $json = file_get_contents($path);

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    protected function fakeJson(string $urlPattern, string $exampleFile, int $status = 200, array $headers = []): void
    {
        $payload = $this->example($exampleFile);
        Http::fake([
            $urlPattern => Http::response(
                $payload,
                $status,
                array_merge(['Content-Type' => 'application/json'], $headers)
            ),
        ]);
    }

    protected function assertPagination(array $json, int $perPage, ?int $total = null): void
    {
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('meta', $json);
        $this->assertSame($perPage, $json['meta']['per_page'] ?? null);
        if ($total !== null) {
            $this->assertSame($total, $json['meta']['total'] ?? null);
        }
    }

    protected function assertApiError(array $json, string $code, int $status): void
    {
        $this->assertArrayHasKey('error', $json);
        $this->assertSame($code, $json['error']['code'] ?? null);
        $this->assertSame($status, $json['error']['status'] ?? null);
    }
}
