<?php

namespace Shiwang\CpanelLaravelSdk\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException;
use Shiwang\CpanelLaravelSdk\Facades\Cpanel;
use Shiwang\CpanelLaravelSdk\Tests\TestCase;

class SessionManagerTest extends TestCase
{
    public function test_create_sends_expected_request_and_returns_decoded_response(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/create_user_session*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'Created session', 'command' => 'create_user_session', 'version' => 1],
                'data' => ['url' => 'https://whm.example.com:2083/cpsess1234/login/?session=abc', 'service' => 'cpaneld'],
            ]),
        ]);

        $response = Cpanel::sessions()->create('newuser', 'cpaneld');

        $this->assertSame(1, $response['metadata']['result']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'json-api/create_user_session')
                && $request->method() === 'POST'
                && $request['user'] === 'newuser'
                && $request['service'] === 'cpaneld'
                && ! array_key_exists('app', $request->data())
                && ! array_key_exists('locale', $request->data());
        });
    }

    public function test_cpanel_login_url_returns_url_from_data(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/create_user_session*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'Created session', 'command' => 'create_user_session', 'version' => 1],
                'data' => ['url' => 'https://whm.example.com:2083/cpsess1234/login/?session=abc', 'service' => 'cpaneld'],
            ]),
        ]);

        $url = Cpanel::sessions()->cpanelLoginUrl('newuser');

        $this->assertSame('https://whm.example.com:2083/cpsess1234/login/?session=abc', $url);

        Http::assertSent(fn ($request) => $request['service'] === 'cpaneld');
    }

    public function test_webmail_login_url_sends_webmaild_service(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/create_user_session*' => Http::response([
                'metadata' => ['result' => 1, 'reason' => 'Created session', 'command' => 'create_user_session', 'version' => 1],
                'data' => ['url' => 'https://whm.example.com:2096/cpsess1234/login/?session=abc', 'service' => 'webmaild'],
            ]),
        ]);

        $url = Cpanel::sessions()->webmailLoginUrl('newuser', 'roundcube');

        $this->assertSame('https://whm.example.com:2096/cpsess1234/login/?session=abc', $url);

        Http::assertSent(fn ($request) => $request['service'] === 'webmaild'
            && $request['app'] === 'roundcube');
    }

    public function test_create_fails_throws_exception(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/create_user_session*' => Http::response([
                'metadata' => ['result' => 0, 'reason' => 'No such user', 'command' => 'create_user_session', 'version' => 1],
            ]),
        ]);

        $this->expectException(WhmRequestException::class);
        $this->expectExceptionMessage('No such user');

        Cpanel::sessions()->create('ghost');
    }
}
