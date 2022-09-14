<?php

namespace BernardWiesner\PubSub\Facades;

use Illuminate\Support\Facades\Facade;
use BernardWiesner\PubSub\PubSubClient;
use BernardWiesner\PubSub\PubSubFake;

/**
 * @see \Google\Cloud\PubSub\PubSubClient
 */
class PubSub extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return PubSubClient::class;
    }

    public static function fake()
    {
        static::swap($fake = new PubSubFake(static::getFacadeRoot()));

        return $fake;
    }
}
