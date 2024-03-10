<?php

namespace GregPriday\ZyteApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array extract(array $urls, ?callable $processCallback = null, array $additionalArgs = [])
 * @method static string|array extractHttpBody(string|array $url)
 * @method static string|array extractBrowserHtml(string|array $url)
 * @method static \stdClass|array extractArticle(string|array $url)
 * @method static string htmlToCleanMarkdown(string $html)
 *
 * @see \GregPriday\ZyteApi\ZyteApi
 */
class ZyteApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \GregPriday\ZyteApi\ZyteApi::class;
    }
}
