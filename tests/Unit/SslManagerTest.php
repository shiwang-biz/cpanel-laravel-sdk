<?php

namespace Shiwang\CpanelLaravelSdk\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException;
use Shiwang\CpanelLaravelSdk\Facades\Cpanel;
use Shiwang\CpanelLaravelSdk\Tests\TestCase;

class SslManagerTest extends TestCase
{
    public function test_install_sends_expected_request_and_returns_decoded_response(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/installssl*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'installssl', 'version' => 1],
            ]),
        ]);

        $response = Cpanel::ssl()->install('example.com', 'CERT-DATA', 'KEY-DATA', 'CABUNDLE-DATA');

        $this->assertSame(1, $response['metadata']['result']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'json-api/installssl')
                && $request->method() === 'POST'
                && $request['domain'] === 'example.com'
                && $request['crt'] === 'CERT-DATA'
                && $request['key'] === 'KEY-DATA'
                && $request['cabundle'] === 'CABUNDLE-DATA';
        });
    }

    public function test_install_omits_null_ca_bundle(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/installssl*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'installssl', 'version' => 1],
            ]),
        ]);

        Cpanel::ssl()->install('example.com', 'CERT-DATA', 'KEY-DATA');

        Http::assertSent(fn ($request) => ! array_key_exists('cabundle', $request->data()));
    }

    public function test_info_uses_get_request(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/fetchsslinfo*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'fetchsslinfo', 'version' => 1],
                'data' => ['crt' => []],
            ]),
        ]);

        $response = Cpanel::ssl()->info('example.com');

        $this->assertSame([], $response['data']['crt']);

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), 'json-api/fetchsslinfo')
            && $request['domain'] === 'example.com');
    }

    public function test_run_auto_ssl_fails_throws_exception(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/start_autossl_check*' => Http::response([
                'metadata' => ['result' => 0, 'reason' => 'No such user', 'command' => 'start_autossl_check', 'version' => 1],
            ]),
        ]);

        $this->expectException(WhmRequestException::class);
        $this->expectExceptionMessage('No such user');

        Cpanel::ssl()->runAutoSsl('ghost');
    }
}
