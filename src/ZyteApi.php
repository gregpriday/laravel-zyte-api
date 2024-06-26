<?php

namespace GregPriday\ZyteApi;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleRetry\GuzzleRetryMiddleware;
use Illuminate\Support\Arr;

class ZyteApi
{
    const CONCURRENCY = 5;

    const API_ENDPOINT = 'https://api.zyte.com/v1/extract';

    private Client $client;

    private string $apiKey;

    private string $endpoint;

    private int $concurrency;

    public static $defaultArgs = [
        'httpResponseBody' => true,
    ];

    public function __construct(?string $apiKey = null, ?string $endpoint = null, ?Client $client = null, int $concurrency = self::CONCURRENCY)
    {
        // Set the API key from the parameter or from a configuration/environment variable
        $this->apiKey = $apiKey ?? '';
        $this->endpoint = $endpoint ?? self::API_ENDPOINT;

        // Create a Guzzle handler stack and add the retry middleware
        if (! $client) {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'retries_enabled' => true,
                'max_retry_attempts' => 5,
            ]));

            // Initialize the Guzzle client with auth configuration and handler stack
            $this->client = new Client([
                'base_uri' => $this->endpoint,
                'headers' => ['Accept-Encoding' => 'gzip'],
                'auth' => [$apiKey, ''],
                'handler' => $stack,
            ]);
        } else {
            $this->client = $client;
        }
        $this->concurrency = $concurrency;
    }

    protected function requestFactory(array $body): Request
    {
        return new Request(
            'POST',
            self::API_ENDPOINT,
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip',
                'Authorization' => 'Basic '.base64_encode($this->apiKey.':'),
            ],
            json_encode($body)
        );
    }

    /**
     * Extract data from URLs using callback for processing.
     *
     * @param  array|string  $urls  The URLs to extract from
     * @param  array  $args  Additional request parameters
     * @param  callable|null  $processCallback  A callback to process each response
     */
    public function extract(array|string $urls, array $args = [], ?callable $processCallback = null): mixed
    {
        $args = ! empty($args) ? $args : self::$defaultArgs;

        $isSingle = is_string($urls);
        $urls = Arr::wrap($urls);

        $requests = function ($urls, $additionalArgs) {
            foreach ($urls as $url) {
                yield function () use ($url, $additionalArgs) {
                    return $this->client->sendAsync($this->requestFactory(array_merge([
                        'url' => $url,
                    ], $additionalArgs)));
                };
            }
        };

        $responses = [];

        $pool = new Pool($this->client, $requests($urls, $args), [
            'concurrency' => $this->concurrency,
            'fulfilled' => function ($response, $index) use (&$responses, $urls, $processCallback) {
                // If a callback for processing data is provided, use it
                $response = $response->getBody()->getContents();
                $response = json_decode($response, true);
                // Automatically decode the http response body
                if (isset($response['httpResponseBody'])) {
                    $response['httpResponseBody'] = base64_decode($response['httpResponseBody']);
                }

                $responses[$urls[$index]] = $processCallback ? $processCallback($response) : $response;
            },
            'rejected' => function ($reason, $index) use (&$responses, $urls) {
                $responses[$urls[$index]] = $reason;
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        return $isSingle ? $responses[$urls[0]] : $responses;
    }
}
