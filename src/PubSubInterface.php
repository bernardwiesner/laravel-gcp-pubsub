<?php

namespace BernardWiesner\PubSub;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;

interface PubSubInterface
{

    public function topic(string $name): Builder;

    public function delaySeconds(int $seconds): Builder;

    public function publish(string $topic, array $message, int $delaySeconds = 0): array;

    public function acknowledge(Subscription $subscription, Message $message): void;
}
