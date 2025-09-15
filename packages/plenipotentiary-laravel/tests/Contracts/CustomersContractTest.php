<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Tests\Contracts;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Group;

#[Group('contracts')]
final class CustomersContractTest extends ApiContractTestCase
{
    public function it_conforms_to_index_contract(): void
    {
        $this->fakeJson('*/customers*', 'customers/index.json');

        $res = Http::get('https://plenipotentiary-sandbox.test/acmecart/backoffice/customers?page=1');
        $this->assertTrue($res->ok(), 'Response not OK');

        $json = $res->json();
        $this->assertPagination($json, perPage: 2, total: 2);
        $this->assertCount(2, $json['data']);
        $this->assertSame('C-1001', $json['data'][0]['id']);
    }
}
