# Overview

Wrapper over Google Cloud PubSub for PHP: https://github.com/googleapis/google-cloud-php-pubsub. 

This wrapper provides a fluent API and has some additional features such as `delaySeconds()`. It also provides a mock facade for testing.

### Installation

```sh
$ composer require bernardwiesner/laravel-gcp-pubsub
```

### Transport protocol

By default the wrapper is using http 1.1 REST. I have tested with gRPC and observed a slower response rate. If you would like to use gRPC instead you can do the following:

```sh
$ php artisan vendor:publish --tag=gcp-pubsub
```

Then you can edit the `config/gcp-pubsub.php` file and modify the `transport` key from `rest` to `grcp`.

```php
<?
return [
    // rest or grcp
    'transport' => 'grcp',
    // The timeout in seconds when communicating with GCP
    'timeout' => 10
];
```

### Fluent API

You can chain the API by using the fluent syntax:

```php
    use PubSub;
    // ...
    PubSub::topic('your-topic')
    ->delaySeconds(30)
    ->publish('your body', [
        "some-attribute" => "some-value"
    ]);
```

#### delaySeconds

This will add an attribute called `available_at` to the request payload. You can use this attribute to determine if your job is due to be processed. For exampe if you configure your pubsub subscription as `push` you can check the `available_at` attribute in your API and return early if the time is not yet met.


### Testing

You can mock the PubSub facade in your test by doing:

```php
PubSub::fake();
```

You can also verify the topics published by doing:

```php
$this->assertEquals(1, count(PubSub::getPublished()));
```

You can also assert a single topic was published to:

```php
PubSub::assertPublished('your-topic');
```