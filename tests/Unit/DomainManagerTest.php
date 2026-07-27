<?php

namespace Shiwang\CpanelLaravelSdk\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException;
use Shiwang\CpanelLaravelSdk\Facades\Cpanel;
use Shiwang\CpanelLaravelSdk\Tests\TestCase;

class DomainManagerTest extends TestCase
{
    public function test_add_addon_domain_sends_expected_proxy_request(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/cpanel*' => Http::response([
                'cpanelresult' => [
                    'apiversion' => 2,
                    'module' => 'AddonDomain',
                    'func' => 'addondomain',
                    'event' => ['result' => 1],
                    'data' => [['result' => 1]],
                ],
            ]),
        ]);

        $response = Cpanel::domains()->addAddonDomain('cpuser', 'addon.com', 'addon', 'public_html/addon');

        $this->assertSame(1, $response['cpanelresult']['event']['result']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'json-api/cpanel')
                && $request->method() === 'POST'
                && $request['cpanel_jsonapi_user'] === 'cpuser'
                && $request['cpanel_jsonapi_apiversion'] === 2
                && $request['cpanel_jsonapi_module'] === 'AddonDomain'
                && $request['cpanel_jsonapi_func'] === 'addondomain'
                && $request['newdomain'] === 'addon.com'
                && $request['subdomain'] === 'addon'
                && $request['dir'] === 'public_html/addon';
        });
    }

    public function test_add_addon_domain_fails_throws_exception_with_cpanel_event_error(): void
    {
        Http::fake([
            'whm.example.com:2087/json-api/cpanel*' => Http::response([
                'cpanelresult' => [
                    'apiversion' => 2,
                    'event' => ['result' => 0, 'errors' => ['Domain is already in use.']],
                    'data' => [],
                ],
            ]),
        ]);

        $this->expectException(WhmRequestException::class);
        $this->expectExceptionMessage('Domain is already in use.');

        Cpanel::domains()->addAddonDomain('cpuser', 'taken.com', 'taken', 'public_html/taken');
    }

    public function test_add_subdomain_sends_expected_proxy_request(): void
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

        Cpanel::domains()->addSubdomain('cpuser', 'blog', 'example.com', 'public_html/blog');

        Http::assertSent(function ($request) {
            return $request['cpanel_jsonapi_module'] === 'SubDomain'
                && $request['cpanel_jsonapi_func'] === 'addsubdomain'
                && $request['domain'] === 'blog'
                && $request['rootdomain'] === 'example.com'
                && $request['dir'] === 'public_html/blog';
        });
    }

    public function test_park_domain_sends_expected_proxy_request(): void
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

        Cpanel::domains()->parkDomain('cpuser', 'parked.com', 'example.com');

        Http::assertSent(function ($request) {
            return $request['cpanel_jsonapi_module'] === 'Park'
                && $request['cpanel_jsonapi_func'] === 'park'
                && $request['domain'] === 'parked.com'
                && $request['topdomain'] === 'example.com';
        });
    }

    public function test_list_parked_domains_uses_get_request(): void
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

        Cpanel::domains()->listParkedDomains('cpuser');

        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request['cpanel_jsonapi_module'] === 'Park'
            && $request['cpanel_jsonapi_func'] === 'listparkeddomains');
    }
}
