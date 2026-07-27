<?php

namespace Shiwang\CpanelLaravelSdk\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException;
use Shiwang\CpanelLaravelSdk\Facades\Cpanel;
use Shiwang\CpanelLaravelSdk\Tests\TestCase;

class AccountManagerTest extends TestCase
{
    public function test_create_account_sends_expected_request_and_returns_decoded_response(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/createacct*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'createacct', 'version' => 1],
                'data' => ['result' => 1],
            ]),
        ]);

        $response = Cpanel::accounts()->create([
            'username' => 'newuser',
            'domain' => 'example.com',
            'password' => 'S3cur3Pass!',
            'plan' => 'default',
        ]);

        $this->assertSame(1, $response['metadata']['result']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'json-api/createacct')
                && $request->method() === 'POST'
                && $request['username'] === 'newuser'
                && $request['domain'] === 'example.com'
                && $request->hasHeader('Authorization');
        });
    }

    public function test_failed_whm_result_throws_exception(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/suspendacct*' => Http::response([
                'metadata' => ['result' => 0, 'reason' => 'No such user', 'command' => 'suspendacct', 'version' => 1],
            ]),
        ]);

        $this->expectException(WhmRequestException::class);
        $this->expectExceptionMessage('No such user');

        Cpanel::accounts()->suspend('ghost');
    }

    public function test_list_accounts_uses_get_request(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/listaccts*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'OK', 'command' => 'listaccts', 'version' => 1],
                'data' => ['acct' => []],
            ]),
        ]);

        $response = Cpanel::accounts()->list();

        $this->assertSame([], $response['data']['acct']);

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_contains($request->url(), 'json-api/listaccts'));
    }
}
