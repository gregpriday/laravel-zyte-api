# Laravel Zyte API

Laravel Zyte API is a powerful Laravel package that seamlessly integrates Zyte's web scraping capabilities into your Laravel applications. With a focus on flexibility and ease of use, this package simplifies the process of extracting data from web pages using Zyte's advanced data extraction API.

## Features

- Easy integration with Zyte API services through a simple Laravel facade
- Flexible `extract` method for concurrent fetching of multiple URLs
- Support for extracting raw HTML, browser-rendered HTML, structured article data, and more
- Customizable concurrency for efficient handling of batch requests
- Built-in retry logic for robust handling of API request failures
- Utilizes GuzzleHttp for efficient communication with the Zyte API
- Provides a `Processors` class with utility methods for processing extracted data, such as converting HTML to clean Markdown

## Installation

Install the package via Composer by running the following command in your Laravel project directory:

```bash
composer require gregpriday/laravel-zyte-api
```

## Configuration

To configure your Zyte API key, proxy, and other settings, publish the package configuration file:

```bash
php artisan vendor:publish --provider="GregPriday\ZyteApi\ZyteApiServiceProvider"
```

This command will create a `config/zyte.php` file in your project. Open this file to review the available configuration options.

Next, add the following entries to your `.env` file:

```
ZYTE_API_KEY=your-zyte-api-key-here
ZYTE_API_CONCURRENCY=5
ZYTE_PROXY=your-proxy-url-here
```

Make sure to replace `your-zyte-api-key-here` with your actual Zyte API key and `your-proxy-url-here` with the appropriate proxy URL.

## Usage

### Extracting Data

To extract data from a URL or multiple URLs, use the `extract` method:

```php
use GregPriday\ZyteApi\Facades\ZyteApi;

$url = 'https://example.com';
$response = ZyteApi::extract($url, ['browserHtml' => true]);

// Access the browser-rendered HTML
echo $response['browserHtml'];

$urls = [
    'https://example.com/article1',
    'https://example.com/article2',
    'https://example.com/article3',
];

$responses = ZyteApi::extract($urls, ['article' => true]);

// Access the extracted article data for each URL
foreach ($responses as $url => $articleData) {
    echo "URL: $url\n";
    echo "Headline: {$articleData['headline']}\n";
    echo "Article Body: {$articleData['articleBody']}\n";
    // ...
}
```

The `extract` method accepts the following parameters:

- `$urls` (array|string): A single URL or an array of URLs to extract data from.
- `$args` (array): Additional request parameters to customize the extraction process. Refer to the Zyte API documentation for available options.
- `$processCallback` (callable|null): An optional callback function to process each API response. If not provided, the raw API response will be returned.

### Handling Errors

The package includes built-in retry logic to handle API request failures. If a request fails, it will automatically retry up to 5 times before returning an error message. You can customize the retry behavior by modifying the `GuzzleRetryMiddleware` configuration in the `ZyteApi` constructor.

### Using the Zyte Proxy

To make requests through the Zyte proxy, you can use the `ZyteClient` class:

```php
use GregPriday\ZyteApi\Proxy\ZyteClient;

$client = app(ZyteClient::class);
$response = $client->get('https://example.com');

$httpResponseBody = $response->getBody()->getContents();
// ...
```

Make sure to set the `ZYTE_PROXY` value in your `.env` file to the appropriate proxy URL.

## Processors

The package includes a `Processors` class that provides utility methods for processing extracted data. For example, the `htmlToCleanMarkdown` method can be used to convert HTML content to clean Markdown format:

```php
use GregPriday\ZyteApi\Facades\ZyteApi;
use GregPriday\ZyteApi\Processors;

$urls = [
    'https://example.com/article1',
    'https://example.com/article2',
    'https://example.com/article3',
];

$responses = ZyteApi::extract($urls, ['article' => true], function ($response) {
    $articleData = $response['article'];

    // Convert article HTML content to Markdown
    $articleData['articleBodyMarkdown'] = Processors::htmlToCleanMarkdown($articleData['articleBodyHtml']);

    return $articleData;
});

// Access the extracted article data and converted Markdown content for each URL
foreach ($responses as $url => $articleData) {
    echo "URL: $url\n";
    echo "Headline: {$articleData['headline']}\n";
    echo "Article Body (HTML): {$articleData['articleBodyHtml']}\n";
    echo "Article Body (Markdown): {$articleData['articleBodyMarkdown']}\n";
    // ...
}
```

## Testing

The package includes PHPUnit tests to ensure its functionality. To run the tests, navigate to your project directory and execute:

```bash
vendor/bin/phpunit
```

Make sure to configure your test environment properly to prevent real API calls to Zyte during testing.

## Contributing

Contributions to the Laravel Zyte API package are welcome! If you find any bugs, have suggestions for improvements, or want to add new features, please submit a pull request. Ensure that your contributions adhere to the project's coding standards and conventions.

## License

The Laravel Zyte API package is open-source software licensed under the [MIT License](http://opensource.org/licenses/MIT).
