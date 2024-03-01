<?php

namespace GregPriday\ZyteApi;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleRetry\GuzzleRetryMiddleware;
use Illuminate\Support\Arr;
use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Component\DomCrawler\Crawler;

class ZyteApi
{
    const CONCURRENCY = 5;

    const API_ENDPOINT = 'https://api.zyte.com/v1/extract';

    private Client $client;

    private string $apiKey;

    private string $endpoint;

    public function __construct(?string $apiKey = null, ?string $endpoint = null, ?Client $client = null)
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
     * @param  array  $urls  The URLs to extract from
     * @param  callable|null  $processCallback  A callback to process each response
     * @param  array  $additionalArgs  Additional request parameters
     */
    protected function extract(array $urls, ?callable $processCallback = null, array $additionalArgs = []): array
    {
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

        $pool = new Pool($this->client, $requests($urls, $additionalArgs), [
            'concurrency' => self::CONCURRENCY,
            'fulfilled' => function ($response, $index) use (&$responses, $urls, $processCallback) {
                $data = $response->getBody()->getContents();

                // If a callback for processing data is provided, use it
                $responses[$urls[$index]] = $processCallback ? $processCallback($data) : $data;
            },
            'rejected' => function ($reason, $index) use (&$responses, $urls) {
                $responses[$urls[$index]] = 'Error: '.$reason->getMessage();
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        return $responses;
    }

    /**
     * Extract the HTML from a URL.
     *
     * @param  string|array  $url  The URL to extract from
     */
    public function extractHttpBody(string|array $url): string|array
    {
        $returnSingle = is_string($url);
        $urls = Arr::wrap($url);

        $result = $this->extract($urls, function ($data) {
            $decodedData = json_decode($data);

            return base64_decode($decodedData->httpResponseBody ?? '');
        }, ['httpResponseBody' => true]);

        return $returnSingle ? $result[$urls[0]] : $result;
    }

    /**
     * Extract the HTML from URLs using the browser engine.
     *
     * @param  string|array  $url  The URL(s) to extract from
     */
    public function extractBrowserHtml(string|array $url): string|array
    {
        $returnSingle = is_string($url);
        $urls = Arr::wrap($url);

        $result = $this->extract($urls, function ($data) {
            $decodedData = json_decode($data);

            return $decodedData->browserHtml ?? '';
        }, ['browserHtml' => true]);

        return $returnSingle ? $result[$urls[0]] : $result;
    }

    /**
     * Extract an article using the Zyte API.
     *
     * @param  string|array  $url  The URL(s) to extract from
     */
    public function extractArticle(string|array $url): stdClass|array
    {
        $returnSingle = is_string($url);
        $urls = Arr::wrap($url);

        $result = $this->extract($urls, function ($data) {
            return json_decode($data);
        }, ['article' => true]);

        return $returnSingle ? $result[$urls[0]] : $result;
    }

    public function getArticleContent(string|array $url): string|array
    {
        $returnSingle = is_string($url);
        $urls = Arr::wrap($url);

        // Extract the article HTML using the Zyte API
        $result = $this->extract($urls, function ($data) {
            $decodedData = json_decode($data);
            $article = $decodedData->article->articleBodyHtml ?? '';
            $article = $this->htmlToCleanMarkdown($article);

            // If we don't have an article body, then we don't want to return anything.
            if (empty($article)) {
                return [
                    'meta' => [],
                    'content' => '',
                ];
            }

            $meta = [];
            $meta['url'] = $decodedData->url;
            $meta['headline'] = $decodedData->article->headline ?? '';

            $htmlTitle = $this->getTitleFromHtml($decodedData->browserHtml ?? '');
            if (! empty($htmlTitle)) {
                $meta['html_title'] = $htmlTitle;
            }
            if (! empty($decodedData->article->datePublished)) {
                $meta['published_on'] = Carbon::parse($decodedData->article->datePublished)->format('j F Y');
            }
            if (! empty($decodedData->article->authors) && is_array($decodedData->article->authors)) {
                $meta['authors'] = array_map(fn ($a) => $a->name, $decodedData->article->authors);
            }

            return [
                'meta' => $meta,
                'content' => $article,
            ];
        }, ['article' => true, 'browserHtml' => true]);

        return $returnSingle ? $result[$urls[0]] : $result;
    }

    private function htmlToCleanMarkdown(string $html): string
    {
        $crawler = new Crawler($html);
        $crawler->filter('figure, iframe, audio, video, img')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });
        $cleanHtml = $crawler->html();
        $cleanHtml = strip_tags($cleanHtml, '<h1><h2><h3><h4><h5><h6><p><a><ul><ol><li>');

        $converter = new HtmlConverter([
            'header_style' => 'atx',
            'strip_tags' => true,
        ]);

        $markdown = $converter->convert($cleanHtml);

        return $markdown;
    }

    private function getTitleFromHtml(string $html): ?string
    {
        $crawler = new Crawler($html);
        $title = $crawler->filter('head > title');

        return $title->count() > 0 ? $title->text() : null;
    }
}
