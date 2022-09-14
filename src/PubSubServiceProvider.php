<?php

namespace BernardWiesner\PubSub;

use Google\Auth\Cache\SysVCacheItemPool;
use Google\Cloud\PubSub\PubSubClient as GooglePubSubClient;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class PubSubServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PubSubClient::class, function () {
            if (App::environment('local')) {
                return new PubSubFake();
            } else {
                return new PubSubClient(
                    new GooglePubSubClient([
                        'requestTimeout' => Config::get('gcp-pubsub.timeout', 10),
                        'transport' => Config::get('gcp-pubsub.transport', 'rest'),
                        'authCache' => new SysVCacheItemPool()
                    ])
                );
            }
        });
    }

    /** 
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/gcp-pubsub.php' => $this->app->configPath('gcp-pubsub.php')
        ], 'gcp-pubsub');
    }
}
