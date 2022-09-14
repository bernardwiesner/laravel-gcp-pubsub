<?php

namespace BernardWiesner\PubSub;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;
use BernardWiesner\PubSub\PubSubInterface;
use PHPUnit\Framework\Assert as PHPUnit;

class PubSubFake implements PubSubInterface
{
    use Formatter;

    private $publishedTopics = [];

    public function delaySeconds(int $seconds): Builder
    {
        return (new Builder($this))->delaySeconds($seconds);
    }

    public function topic(string $name): Builder
    {
        return (new Builder($this))->topic($name);
    }

    public function publish(string $topic, array $message, int $delaySeconds = 0): array
    {
        $message = $this->formatMessage($message, $delaySeconds);
        $this->publishedTopics[$topic] = $message;
        return [];
    }

    public function modifyAckDeadline(Subscription $subscription, Message $message, int $delaySeconds = 0): void
    {
    }

    public function acknowledge(Subscription $subscription, Message $message): void
    {
    }

    public function assertPublished(string $topic)
    {
        $published = isset($this->publishedTopics[$topic]);
        PHPUnit::assertTrue($published);
    }

    public function getPublished(string $topic = null): array
    {
        if (!$topic) {
            return $this->publishedTopics;
        }
        if (isset($this->publishedTopics[$topic])) {
            return $this->publishedTopics[$topic];
        }
        return [];
    }
}
