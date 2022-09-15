<?php

namespace BernardWiesner\PubSub;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient as GooglePubSubClient;
use Google\Cloud\PubSub\Subscription;
use Illuminate\Support\Facades\Config;

class PubSubClient implements PubSubInterface
{

    use Formatter;

    private GooglePubSubClient $client;

    private int $retryTimes;

    private int $retryWaitMilliseconds;

    public function __construct(GooglePubSubClient $client)
    {
        $this->client = $client;
        $this->retryTimes = Config::get('gcp-pubsub.retry', 2);
        $this->retryWaitMilliseconds = Config::get('gcp-pubsub.retry_wait', 100);
    }

    public function topic(string $name): Builder
    {
        return (new Builder($this))->topic($name);
    }

    public function delaySeconds(int $seconds): Builder
    {
        if ($seconds < 10) {
            throw new \UnexpectedValueException("Can only delay for >= 10, $seconds given. (pub/sub spec)");
        }
        return (new Builder($this))->delaySeconds($seconds);
    }

    public function publish(string $topic, array $message, int $delaySeconds = 0): array
    {
        $topic = $this->client->topic($topic);
        $message = $this->formatMessage($message, $delaySeconds);
        return retry($this->retryTimes, function () use ($topic, $message) {
            return $topic->publish($message);
        }, $this->retryWaitMilliseconds);
    }

    /**
     * @return Message[]
     */
    public function pull(string $subscription, array $options = [])
    {
        $this->client->subscription($subscription)->pull($options);
    }

    public function acknowledge(Subscription $subscription, Message $message): void
    {
        retry($this->retryTimes, function () use ($subscription, $message) {
            $subscription->acknowledge($message);
        }, $this->retryWaitMilliseconds);
    }
}
