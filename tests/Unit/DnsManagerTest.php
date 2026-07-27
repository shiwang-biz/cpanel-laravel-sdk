<?php

namespace Shiwang\CpanelLaravelSdk\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException;
use Shiwang\CpanelLaravelSdk\Facades\Cpanel;
use Shiwang\CpanelLaravelSdk\Tests\TestCase;

class DnsManagerTest extends TestCase
{
    public function test_create_zone_sends_expected_request_and_returns_decoded_response(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/adddns*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'adddns', 'version' => 1],
            ]),
        ]);

        $response = Cpanel::dns()->create('example.com', '192.0.2.10');

        $this->assertSame(1, $response['metadata']['result']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'json-api/adddns')
                && $request->method() === 'POST'
                && $request['domain'] === 'example.com'
                && $request['ip'] === '192.0.2.10';
        });
    }

    public function test_delete_zone_fails_throws_exception(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/killdns*' => Http::response([
                'metadata' => ['result' => 0, 'reason' => 'No such zone', 'command' => 'killdns', 'version' => 1],
            ]),
        ]);

        $this->expectException(WhmRequestException::class);
        $this->expectExceptionMessage('No such zone');

        Cpanel::dns()->delete('ghost.com');
    }

    public function test_dump_zone_uses_get_request(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/dumpzone*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'dumpzone', 'version' => 1],
                'data' => ['record' => []],
            ]),
        ]);

        $response = Cpanel::dns()->dump('example.com');

        $this->assertSame([], $response['data']['record']);

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), 'json-api/dumpzone')
            && $request['domain'] === 'example.com');
    }

    public function test_reset_zone_sends_post_request(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/resetzone*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'resetzone', 'version' => 1],
            ]),
        ]);

        Cpanel::dns()->reset('example.com');

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && str_contains($request->url(), 'json-api/resetzone')
            && $request['domain'] === 'example.com');
    }

    public function test_edit_zone_merges_domain_into_params(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/editzone*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'editzone', 'version' => 1],
            ]),
        ]);

        Cpanel::dns()->editZone('example.com', ['serial' => '2026072801']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'json-api/editzone')
                && $request->method() === 'POST'
                && $request['domain'] === 'example.com'
                && $request['serial'] === '2026072801';
        });
    }
}
