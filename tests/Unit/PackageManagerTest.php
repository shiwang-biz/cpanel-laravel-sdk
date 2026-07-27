<?php

namespace Shiwang\CpanelLaravelSdk\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException;
use Shiwang\CpanelLaravelSdk\Facades\Cpanel;
use Shiwang\CpanelLaravelSdk\Tests\TestCase;

class PackageManagerTest extends TestCase
{
    public function test_create_package_sends_expected_request_and_returns_decoded_response(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/addpkg*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'addpkg', 'version' => 1],
            ]),
        ]);

        $response = Cpanel::packages()->create([
            'name' => 'gold',
            'quota' => 5000,
            'bwlimit' => 10000,
        ]);

        $this->assertSame(1, $response['metadata']['result']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'json-api/addpkg')
                && $request->method() === 'POST'
                && $request['name'] === 'gold'
                && $request['quota'] === 5000;
        });
    }

    public function test_update_package_merges_name_into_params(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/editpkg*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'editpkg', 'version' => 1],
            ]),
        ]);

        Cpanel::packages()->update('gold', ['quota' => 10000]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'json-api/editpkg')
                && $request->method() === 'POST'
                && $request['name'] === 'gold'
                && $request['quota'] === 10000;
        });
    }

    public function test_delete_package_fails_throws_exception(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/killpkg*' => Http::response([
                'metadata' => ['result' => 0, 'reason' => 'No such package', 'command' => 'killpkg', 'version' => 1],
            ]),
        ]);

        $this->expectException(WhmRequestException::class);
        $this->expectExceptionMessage('No such package');

        Cpanel::packages()->delete('ghost');
    }

    public function test_list_packages_uses_get_request(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/listpkgs*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'listpkgs', 'version' => 1],
                'data' => ['pkg' => []],
            ]),
        ]);

        $response = Cpanel::packages()->list();

        $this->assertSame([], $response['data']['pkg']);

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), 'json-api/listpkgs'));
    }
}
