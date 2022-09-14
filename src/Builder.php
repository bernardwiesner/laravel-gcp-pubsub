<?php

namespace BernardWiesner\PubSub;

class Builder
{

    private int $delaySeconds = 0;

    private string $topic;

    private PubSubInterface $client;

    public function __construct(PubSubInterface $client)
    {
        $this->client = $client;
    }

    public function topic(string $topic): Builder
    {
        $this->topic = $topic;
        return $this;
    }

    public function delaySeconds(int $delaySeconds): Builder
    {
        $this->delaySeconds = $delaySeconds;
        return $this;
    }

    public function publish(array $data, array $attributes = null): array
    {
        $message['data'] = $data;
        if (!empty($attributes)) {
            $message['attributes'] = $attributes;
        }
        return $this->client->publish($this->topic, $message, $this->delaySeconds);
    }
}
