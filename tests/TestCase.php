<?php

namespace Shiwang\CpanelLaravelSdk\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Shiwang\CpanelLaravelSdk\CpanelServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [CpanelServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cpanel.host', 'whm.example.com');
        $app['config']->set('cpanel.port', 2087);
        $app['config']->set('cpanel.username', 'root');
        $app['config']->set('cpanel.password', 'secret');
        $app['config']->set('cpanel.verify_ssl', true);
        $app['config']->set('cpanel.timeout', 30);
    }
}
