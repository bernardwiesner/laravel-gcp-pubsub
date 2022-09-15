<?php

namespace BernardWiesner\PubSub\Tests;

use BernardWiesner\PubSub\PubSubFake;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{

    private PubSubFake $client;

    public function setUp(): void
    {
        $this->client = new PubSubFake();
    }

    public function test_publish_topic()
    {
        $this->client->topic('test-topic')
            ->publish(['some-data'], [
                'foo' => 'bar'
            ]);

        $this->client->assertPublished('test-topic');
        $this->assertEquals(1, count($this->client->getPublished()));
    }

    public function test_publish_topic_without_fluent_api()
    {
        $this->client->publish('test-topic', [
                'data' => ['foo' => 1],
                'attributes' => [
                    'bar' => 2,
                ]
            ], $delaySeconds = 10);

        $this->client->assertPublished('test-topic');
        $published = $this->client->getPublished();

        $this->assertEquals(time() + 10, $published['test-topic']['attributes']['available_at']);
        $this->assertArrayHasKey('foo', json_decode($published['test-topic']['data'], true));
        $this->assertArrayHasKey('bar', $published['test-topic']['attributes']);

    }

    public function test_delay_seconds()
    {
        $this->client->topic('test-topic')
            ->delaySeconds(10)
            ->publish(['some-data']);

        $this->client->assertPublished('test-topic');
        $published = $this->client->getPublished();
        $this->assertEquals(time() + 10, $published['test-topic']['attributes']['available_at']);
    }

    public function test_json_decode_data()
    {
        $this->client->topic('test-topic')
            ->publish(['foo' => 1, 'bar' => 2]);

        $this->client->assertPublished('test-topic');
        $published = $this->client->getPublished();
        $this->assertJson($published['test-topic']['data']);
        $this->assertArrayHasKey('foo', json_decode($published['test-topic']['data'], true));
    }
}
