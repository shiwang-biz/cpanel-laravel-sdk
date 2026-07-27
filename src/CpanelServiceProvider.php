<?php

namespace Shiwang\CpanelLaravelSdk;

use Illuminate\Support\ServiceProvider;

class CpanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cpanel.php', 'cpanel');

        $this->app->singleton(WhmClient::class, function ($app) {
            $config = $app['config']['cpanel'];

            return new WhmClient(
                host: (string) $config['host'],
                port: (int) $config['port'],
                username: (string) $config['username'],
                password: (string) $config['password'],
                verifySsl: (bool) $config['verify_ssl'],
                timeout: (int) $config['timeout'],
            );
        });

        $this->app->singleton(CpanelManager::class, fn ($app) => new CpanelManager(
            $app->make(WhmClient::class)
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cpanel.php' => config_path('cpanel.php'),
            ], 'cpanel-config');
        }
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [WhmClient::class, CpanelManager::class];
    }
}
