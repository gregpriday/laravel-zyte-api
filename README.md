# Laravel Zyte API

Laravel Zyte API is a powerful Laravel package that seamlessly integrates Zyte's web scraping capabilities into your Laravel applications. With a focus on extracting article content, this package simplifies the process of fetching raw HTML, browser-rendered HTML, and structured article data from web pages using Zyte's advanced data extraction API.

## Features

- Easy integration with Zyte API services through a simple Laravel facade
- Methods for extracting raw HTML, browser-rendered HTML, and structured article content
- Configurable concurrency for efficient handling of batch requests
- Built-in retry logic for robust handling of API request failures
- Utilizes GuzzleHttp for efficient communication with the Zyte API
- Automatic registration with Laravel's service provider auto-discovery for easy setup

## Installation

Install the package via Composer by running the following command in your Laravel project directory:

```bash
composer require gregpriday/laravel-zyte-api
```

Laravel's package auto-discovery feature will automatically register the service provider and facade.

## Configuration

To configure your Zyte API key and other settings, publish the package configuration file:

```bash
php artisan vendor:publish --provider="GregPriday\ZyteApi\ZyteApiServiceProvider"
```

This command will create a `config/zyte-api.php` file in your project. Open this file and add your Zyte API key:

```php
return [
    'key' => env('ZYTE_API_KEY', 'your-zyte-api-key-here'),
];
```

For security reasons, it's recommended to store your Zyte API key in your `.env` file:

```
ZYTE_API_KEY=your-zyte-api-key-here
```

## Usage

### Extracting Article Content

To extract article content from a URL, use the `getArticleContent` method:

```php
use GregPriday\ZyteApi\Facades\ZyteApi;

$url = 'http://example.com/some-article';
$articleContent = ZyteApi::getArticleContent($url);

// Access the article content in Markdown format
echo $articleContent['content'];

// Access article metadata (URL, headline, authors, etc.)
print_r($articleContent['meta']);
```

### Extracting Raw or Browser-Rendered HTML

To extract raw HTML or browser-rendered HTML from a URL, use the `extractHttpBody` or `extractBrowserHtml` methods:

```php
$rawHtml = ZyteApi::extractHttpBody($url);
$browserHtml = ZyteApi::extractBrowserHtml($url);

// Output raw HTML content
echo $rawHtml;

// Output browser-rendered HTML content
echo $browserHtml;
```

## Advanced Usage

### Extracting Multiple URLs

You can pass an array of URLs to the `getArticleContent`, `extractHttpBody`, and `extractBrowserHtml` methods to extract data from multiple pages simultaneously:

```php
$urls = [
    'http://example.com/article1',
    'http://example.com/article2',
    'http://example.com/article3',
];

$articleContents = ZyteApi::getArticleContent($urls);
$rawHtmlContents = ZyteApi::extractHttpBody($urls);
$browserHtmlContents = ZyteApi::extractBrowserHtml($urls);
```

The returned data will be an associative array with the URLs as keys and the corresponding content as values.

### Customizing Concurrency

By default, the package uses a concurrency limit of 5 for handling batch requests.

### Handling Errors

The package includes built-in retry logic to handle API request failures. If a request fails, it will automatically retry up to 5 times before returning an error message. You can customize the retry behavior by modifying the `GuzzleRetryMiddleware` configuration in the `ZyteApi` constructor.

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
