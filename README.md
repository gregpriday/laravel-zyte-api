# Laravel Zyte API

Laravel Zyte API is a Laravel extension tailored for integrating the powerful web scraping capabilities of Zyte's API into Laravel applications. Focused predominantly on extracting article content, this package simplifies the process of fetching raw HTML, browser-rendered HTML, or structured article data from web pages through Zyte's advanced data extraction API.

## Features

- Facilitates easy access to Zyte API services through a simple Laravel facade.
- Provides methods for extracting raw HTML, browser-rendered HTML, and structured article content.
- Supports configurable concurrency for handling batch requests efficiently.
- Implements built-in retry logic for robust handling of API request failures.
- Utilizes GuzzleHttp for making HTTP requests, ensuring efficient communication with the Zyte API.
- Automatically registers with Laravel's service provider auto-discovery, simplifying setup.

## Installation

Install the package via Composer by running the following command in your Laravel project directory:

```bash
composer require gregpriday/laravel-zyte-api
```

Laravel's package auto-discovery feature automatically registers the service provider and facade, so there's no need for manual registration.

## Configuration

Publish the package configuration to your project to set up your Zyte API key and other settings:

```bash
php artisan vendor:publish --provider="GregPriday\ZyteApi\ZyteApiServiceProvider"
```

This command creates a `config/zyte-api.php` file. You should edit this file to include your Zyte API key:

```php
return [
    'key' => env('ZYTE_API_KEY', 'your-zyte-api-key-here'),
];
```

Add your Zyte API key to your `.env` file to keep it secure:

```
ZYTE_API_KEY=your-zyte-api-key-here
```

## Usage

### Extracting Article Content

To extract article content from a URL, use the following code:

```php
use GregPriday\ZyteApi\Facades\ZyteApi;

$url = 'http://example.com/some-article';
$articleContent = ZyteApi::getArticleContent($url);

echo $articleContent['content']; // Displays the article content in Markdown
print_r($articleContent['meta']); // Shows article metadata, such as URL, headline, authors, etc.
```

### Extracting Raw or Browser-Rendered HTML

For extracting raw HTML or browser-rendered HTML from a URL, you can do:

```php
$rawHtml = ZyteApi::extractHttpBody($url);
$browserHtml = ZyteApi::extractBrowserHtml($url);

echo $rawHtml; // Outputs raw HTML content
echo $browserHtml; // Outputs browser-rendered HTML content
```

## Testing

The package includes PHPUnit tests. Run the tests by navigating to your project directory and executing:

```bash
vendor/bin/phpunit
```

Make sure your test environment is properly configured to prevent real API calls to Zyte during testing.

## Contributing

Contributions to the Laravel Zyte API package are welcome. Feel free to submit pull requests with improvements, bug fixes, or suggestions. Please ensure your contributions adhere to the project's coding standards and conventions.

## License

The Laravel Zyte API package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
