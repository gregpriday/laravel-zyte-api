<?php

namespace GregPriday\ZyteApi\Proxy;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

class ZyteClient extends Client
{
    public function __construct(?string $proxy = null)
    {
        $proxy = $proxy ?? config('zyte.proxy');

        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory([
            'default_retry_multiplier' => 5,
        ]));

        if (empty($proxy)) {
            throw new NoProxyException();
        }

        parent::__construct([
            'headers' => [
                'X-Crawlera-Profile' => 'desktop',
            ],
            'handler' => $stack,
            'verify' => false,
            'timeout' => 600,
            'proxy' => $proxy,
        ]);
    }
}