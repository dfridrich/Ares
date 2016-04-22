<?php

namespace Defr\Parser;

use Defr\Parser\Helper\StringHelper;
use Defr\ValueObject\Person;
use Symfony\Component\DomCrawler\Crawler;

final class JusticeJednatelPersonParser
{
    /**
     * @param Crawler $crawler
     * @return Person
     */
    public static function parseFromDomCrawler(Crawler $crawler)
    {
        $content = $crawler->filter('.div-cell div div')->text();
        $content = StringHelper::removeEmptyLines($content);

        $contentItems = explode("\n", $content);
        $contentItems = array_map('trim', $contentItems);
        $name = trim(explode(',', $contentItems[0])[0]);

        $birthday = DateTimeParser::parseFromCzechDateString($contentItems[1]);

        return new Person($name, $birthday, $contentItems[2]);
    }
}
