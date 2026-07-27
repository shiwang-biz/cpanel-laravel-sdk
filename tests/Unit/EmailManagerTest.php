<?php

namespace Shiwang\CpanelLaravelSdk\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException;
use Shiwang\CpanelLaravelSdk\Facades\Cpanel;
use Shiwang\CpanelLaravelSdk\Tests\TestCase;

class EmailManagerTest extends TestCase
{
    public function test_create_sends_expected_proxy_request(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/cpanel*' => Http::response([
                'cpanelresult' => [
                    'apiversion' => 2,
                    'event' => ['result' => 1],
                    'data' => [],
                ],
            ]),
        ]);

        $response = Cpanel::email()->create('cpuser', 'example.com', 'info', 'S3cur3Pass!', 500);

        $this->assertSame(1, $response['cpanelresult']['event']['result']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'json-api/cpanel')
                && $request->method() === 'POST'
                && $request['cpanel_jsonapi_user'] === 'cpuser'
                && $request['cpanel_jsonapi_module'] === 'Email'
                && $request['cpanel_jsonapi_func'] === 'addpop'
                && $request['domain'] === 'example.com'
                && $request['email'] === 'info'
                && $request['password'] === 'S3cur3Pass!'
                && $request['quota'] === 500;
        });
    }

    public function test_delete_fails_throws_exception_with_cpanel_event_error(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/cpanel*' => Http::response([
                'cpanelresult' => [
                    'apiversion' => 2,
                    'event' => ['result' => 0, 'errors' => ['No such email account.']],
                    'data' => [],
                ],
            ]),
        ]);

        $this->expectException(WhmRequestException::class);
        $this->expectExceptionMessage('No such email account.');

        Cpanel::email()->delete('cpuser', 'example.com', 'ghost');
    }

    public function test_change_password_sends_expected_proxy_request(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/cpanel*' => Http::response([
                'cpanelresult' => ['apiversion' => 2, 'event' => ['result' => 1], 'data' => []],
            ]),
        ]);

        Cpanel::email()->changePassword('cpuser', 'example.com', 'info', 'N3wPass!');

        Http::assertSent(fn ($request) => $request['cpanel_jsonapi_func'] === 'passwdpop'
            && $request['domain'] === 'example.com'
            && $request['email'] === 'info'
            && $request['password'] === 'N3wPass!');
    }

    public function test_edit_quota_sends_expected_proxy_request(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/cpanel*' => Http::response([
                'cpanelresult' => ['apiversion' => 2, 'event' => ['result' => 1], 'data' => []],
            ]),
        ]);

        Cpanel::email()->editQuota('cpuser', 'example.com', 'info', 1000);

        Http::assertSent(fn ($request) => $request['cpanel_jsonapi_func'] === 'editquota'
            && $request['domain'] === 'example.com'
            && $request['email'] === 'info'
            && $request['quota'] === 1000);
    }

    public function test_list_uses_get_request_and_omits_domain_when_null(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/cpanel*' => Http::response([
                'cpanelresult' => ['apiversion' => 2, 'event' => ['result' => 1], 'data' => []],
            ]),
        ]);

        Cpanel::email()->list('cpuser');

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request['cpanel_jsonapi_func'] === 'listpopswithdisk'
            && ! array_key_exists('domain', $request->data()));
    }

    public function test_list_filters_by_domain_when_given(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/cpanel*' => Http::response([
                'cpanelresult' => ['apiversion' => 2, 'event' => ['result' => 1], 'data' => []],
            ]),
        ]);

        Cpanel::email()->list('cpuser', 'example.com');

        Http::assertSent(fn ($request) => $request['domain'] === 'example.com');
    }
}
