# AsyncRequest documentation

> Asynchronous cURL library for PHP with reasonable API.

PHP is by default single-thread language but when it comes to HTTP requests it is not
very convenient to do them in serial. cURL implementation in PHP offers functions
for multi requests but with terrible C-style API. This library wraps those functions
into modern object-oriented event-driven API.

The following documents are available:

- [Advanced features](#advanced-features)
- [Simple example to call multiple api in asynchronous without response](#simple-example-to-call-multiple-api-in-asynchronous-without-response)
- [Simple example to call multiple api in asynchronous with response](#simple-example-to-call-multiple-api-in-asynchronous-with-response)

## Advanced features

You can specify number of requests that can run in parallel:

```php
$asyncRequest->setParallelLimit(7);
```

You can specify maximum number of milliseconds allowed for cURL functions to execute:

```php
$request = new Asynchronous\Request($url, 300);
```

You can specify time, in seconds, to wait for a response:

```php
$asyncRequest->run(1.5);
```

You can add other requests in callback function:

```php
$callback = function(Asynchronous\Response $response, AsyncRequest\AsyncRequest $asyncRequest) {
    $asyncRequest->enqueue(new AsyncRequest\Request('http://www.example.com'));
};
```

You can specify priority of each request and requests with higher priority will be called first:

```php
$asyncRequest->enqueueWithPriority(10, $request, $callback);
```

If you want to use some cURL options, it is as easy as this:

```php
$request = new Asynchronous\Request($url);
$request->setOption(CURLOPT_POST, true);
```

And if you want some special behavior or some additional data in `Response`, you can always create your own `Request` object by implementing `RequestInterface` interface.

## Simple example to call multiple api in asynchronous without response

```php
$urls = [
    'http://www.example.com',
    'http://www.example.org',
];
$parallel_limit = 7; # number of requests that can be sent in parallel
$curlopt_timeout_ms = 300 # maximum number of milliseconds allowed for cURL functions to execute
$timeout_wait_response = 0.05 # Time, in seconds, to wait for a response.
 
$asyncRequest = new Asynchronous\AsyncRequest();
$asyncRequest->setParallelLimit($parallel_limit);

foreach ($urls as $url) {
    $request = new Asynchronous\Request($url, $curlopt_timeout_ms);
    $asyncRequest->enqueue($request);
}

$asyncRequest->run($timeout_wait_response);
```

## Simple example to call multiple api in asynchronous with response

```php
$urls = [
    'http://www.example.com',
    'http://www.example.org',
];
$parallel_limit = 7; # number of requests that can be sent in parallel
$curlopt_timeout_ms = 1500 # maximum number of milliseconds allowed for cURL functions to execute
$timeout_wait_response = 1.5 # Time, in seconds, to wait for a response.

$asyncRequest = new Asynchronous\AsyncRequest();
$asyncRequest->setParallelLimit($parallel_limit);

foreach ($urls as $url) {
    $request = new Asynchronous\Request($url, $curlopt_timeout_ms);
    $asyncRequest->enqueue($request, function(Asynchronous\Response $response) {
        echo $response->getBody() . "\n";
    });
}

$asyncRequest->run($timeout_wait_response);
```