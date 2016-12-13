<?php

namespace Defr\Parser;

use Defr\Parser\Helper\StringHelper;
use Defr\ValueObject\Person;
use Symfony\Component\DomCrawler\Crawler;

final class JusticeSpolecnikPersonParser
{
    /**
     * @param Crawler $crawler
     *
     * @return Person
     */
    public static function parseFromDomCrawler(Crawler $crawler)
    {
        $content = $crawler->text();
        $content = StringHelper::removeEmptyLines($content);

        $contentItems = explode("\n", $content);
        $contentItems = array_map('trim', $contentItems);
        $name = trim(explode(',', $contentItems[1])[0]);

        $birthday = DateTimeParser::parseFromCzechDateString($contentItems[2]);

        return new Person($name, $birthday, $contentItems[3]);
    }
}
