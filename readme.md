# Overview

Wrapper over [Google Cloud PubSub for PHP](https://github.com/googleapis/google-cloud-php-pubsub). 

This wrapper provides a convenient fluent API and has some usefull features such as timeout, retries and a delay API method. It also provides a mock facade for testing.

The recommended way to use this package is setting up a PubSub API with laravel that processes [push](https://cloud.google.com/pubsub/docs/push) subscription events from GCP pubsub. You can then `publish` topics from any of your microservices in laravel to GCP pubsub, and GCP will call your configured `push` subscription endpoints (APIs) to process the job. This is an alternative over using laravel queue for processing background jobs. 

The advantage of using this package over [laravel queue](https://laravel.com/docs/9.x/queues), is you can easily scale your APIs by simply adding more servers or containers. Another big advantage is it decouples your jobs from your code, there is no need to define your jobs in the same repo as from where they are dispatched/published, allowing for a firendlier microservice architecture. You could even process the jobs in another language other than PHP/laravel in one of your microservices.

For more details on how to setup a PubSub API with laravel refer to [this guide](https://dev.to/bernardwiesner/laravel-microservice-using-gcp-pubsub-an-alternative-to-laravel-queue-2inc).

### Installation

```sh
composer require bernardwiesner/laravel-gcp-pubsub
```

### Publish config

If you would like to adjust the retry attemps, or modify some of the other configurations you should publish the config:

```sh
php artisan vendor:publish --tag=gcp-pubsub
```

### Transport protocol

By default the wrapper is using http 1.1 REST. I have tested with gRPC and observed a slower response rate. If you would like to use gRPC instead you can modify the `transport` inside the `gcp-pubsub.php` config file:

```php
<?
return [
    // rest or grcp
    'transport' => 'grcp',
    // ...
];
```

### Retry

GCP PubSub allows you to configure `retries` on the client, however this feature only works on certain response codes. This package's `retry` settings works on any failed request to GCP, including timeouts. By default retries are set to 2 times with a delay of 100 ms. You can modify these defaults in the `gcp-pubsub.php` file:

```php
<?
return [
    // How many times to retry the request when it fails
    'retry' => 2,
    // How many milliseconds to wait after retry fails
    'retry_wait' => 100
];
```


### Fluent API

You can chain the API by using the fluent syntax:

```php
    use PubSub;
    // ...
    PubSub::topic('your-topic')
    ->delaySeconds(30)
    ->publish(['your data'], [
        "your-attribute" => "your-value"
    ]);
```

#### publish

`publish` accepts 2 parameters, both arrays. The first is the message `data` which is required, and the second is the message `attributes` that is optional (refer to [message format](https://cloud.google.com/pubsub/docs/push#receive_push) on GCP pubsub. The `data` parameter will always get json encoded by this package and base64 encoded if you use `rest`, so you need to json_decode and base64_decode the `data` when you receive the event from GCP pubsub. For example if you are using `push` subscriptions on pubsub, your laravel controller should decode as following:

```php
class YourController extends Controller
{
    public function __invoke(Request $request): HttpResponse
    {
        $message = $request->message;
        $data = json_decode(base64_decode($message['data']), true);
    }
}
```
#### delaySeconds

This will add an attribute called `available_at` to the request payload. You can use this attribute to determine if your job is due to be processed. For exampe if you configure your GCP pubsub subscription as `push` you can check the `available_at` attribute in your API and return early if the time is not yet met.

This is an example of a middleware you can use to return early:

```php
    public function handle(Request $request, Closure $next)
    {
        $availableAt = (int) ($request->message['attributes']['available_at'] ?? 0);
        if ($availableAt > time()) {
            return response()->noContent(409);
        }
        return $next($request);
    }

```

### Without fluent API

You can also opt to avoid using the fluent API and call the underlying API:

```php
    use PubSub;
    // ...
    PubSub::publish('test-topic', [
        'data' => ['foo' => 1],
        'attributes' => [
            'bar' => 2,
        ]
    ], $delaySeconds = 10);
```

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