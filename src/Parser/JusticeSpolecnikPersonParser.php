<?php

namespace Defr\Parser;

use Defr\Parser\Helper\StringHelper;
use Defr\ValueObject\Person;
use Symfony\Component\DomCrawler\Crawler;
use function Sodium\add;

final class JusticeSpolecnikPersonParser
{
    /**
     * @param Crawler $crawler
     *
     * @return Person
     */
    public static function parseFromDomCrawler(Crawler $crawler)
    {

        $name = $crawler->filter('.div-cell div div:nth-child(1) span:nth-child(1)')->text();
        $address = $crawler->filter('.div-cell div div:nth-child(2) span:nth-child(1)')->text();

        $birthday = $crawler->filter('.div-cell div div:nth-child(1) span:nth-child(3)')->text();
        $birthday = DateTimeParser::parseFromCzechDateString($birthday);

        return new Person($name, $birthday, $address);
    }
}
