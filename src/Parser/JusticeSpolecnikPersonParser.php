<?php

namespace Defr\Parser;

use Defr\Parser\Helper\StringHelper;
use Defr\ValueObject\Person;
use Symfony\Component\DomCrawler\Crawler;

final class JusticeSpolecnikPersonParser
{
    /**
     * @return Person
     */
    public static function parseFromDomCrawler(Crawler $crawler)
    {
        $content = $crawler->text();
        $content = StringHelper::removeEmptyLines($content);

        $contentItems = explode(PHP_EOL, $content);

        $name = trim(explode(',', $contentItems[1])[0]);
        $birthday = DateTimeParser::parseFromCzechDateString($contentItems[2]);
        $address = trim($contentItems[3]);

        return new Person($name, $birthday, $address);
    }
}
