<?php

namespace GregPriday\ZyteApi\Proxy;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

class ZyteClient extends Client
{
    const MAX_REDIRECTS = 10;

    const DEFAULT_MAX_RETRIES = 3;

    const DEFAULT_RETRY_MULTIPLIER = 5;

    const DEFAULT_TIMEOUT = 60;

    const DEFAULT_HEADERS = [
        'X-Crawlera-Profile' => 'desktop',
    ];

    const ALLOWED_PROTOCOLS = ['http', 'https'];

    /**
     * @param  string|null  $proxy  Proxy address to be used for the client, or null to fetch from config.
     * @param  array  $config  Additional configuration options that can override defaults.
     *
     * @throws NoProxyException If a proxy is required but not provided.
     */
    public function __construct(?string $proxy = null, array $config = [])
    {
        $proxy = $this->ensureProxy($proxy);
        $stack = $this->createHandlerStack();

        $defaultConfig = $this->getDefaultConfig($proxy, $stack);
        $config = array_merge($defaultConfig, $config);

        parent::__construct($config);
    }

    /**
     * Ensures a proxy is provided, either directly or from configuration.
     *
     * @param  string|null  $proxy  Proxy address that may have been passed directly.
     * @return string The resolved proxy address.
     *
     * @throws NoProxyException If no proxy is provided or resolved.
     */
    private function ensureProxy(?string $proxy): string
    {
        $proxy = $proxy ?? config('zyte.proxy');
        if (empty($proxy)) {
            throw new NoProxyException('Proxy is required but not provided.');
        }

        return $proxy;
    }

    /**
     * Creates and configures a handler stack for the Guzzle client.
     *
     * @return HandlerStack The configured handler stack with retry middleware.
     */
    private function createHandlerStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory([
            'max_retry_attempts' => self::DEFAULT_MAX_RETRIES,
            'default_retry_multiplier' => self::DEFAULT_RETRY_MULTIPLIER,
        ]));

        return $stack;
    }

    /**
     * Builds the default configuration array for the Guzzle client.
     *
     * @param  string  $proxy  The proxy address to use.
     * @param  HandlerStack  $stack  The handler stack to use for the client.
     * @return array The array of default configuration settings.
     */
    private function getDefaultConfig(string $proxy, HandlerStack $stack): array
    {
        return [
            'headers' => self::DEFAULT_HEADERS,
            'handler' => $stack,
            'verify' => false,
            'timeout' => self::DEFAULT_TIMEOUT,
            'proxy' => $proxy,
            'allow_redirects' => [
                'max' => self::MAX_REDIRECTS,
                'strict' => false,
                'referer' => true,
                'protocols' => self::ALLOWED_PROTOCOLS,
                'track_redirects' => true,
            ],
        ];
    }
}
