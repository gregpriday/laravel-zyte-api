<?php

namespace GregPriday\ZyteApi;

use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Component\DomCrawler\Crawler;

class Processors
{
    public static function htmlToCleanMarkdown(string $html): string
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
}
