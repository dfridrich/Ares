<?php

namespace Defr\Parser;

use Defr\Parser\Helper\StringHelper;
use Defr\ValueObject\Person;
use Symfony\Component\DomCrawler\Crawler;

final class JusticeJednatelPersonParser
{
    /**
     * @return Person
     */
    public static function parseFromDomCrawler(Crawler $crawler)
    {
        $content = $crawler->filter('.div-cell div div')->text();
        $content = StringHelper::removeEmptyLines($content);

        $contentItems = explode(PHP_EOL, $content);

        $name = trim(explode(',', $contentItems[0])[0]);
        $birthday = DateTimeParser::parseFromCzechDateString($contentItems[1]);
        $address = trim($contentItems[2]);

        return new Person($name, $birthday, $address);
    }
}
