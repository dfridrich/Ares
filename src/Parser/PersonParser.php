<?php

namespace Defr\Parser;

use Defr\ValueObject\Person;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;
use function Symfony\Component\String\s;

final class PersonParser
{
    public const PARTNER = 'PARTNER';

    public const EXECUTIVE = 'EXECUTIVE';

    public static function parseFromDomCrawler(Crawler $crawler)
    {

        $name = $crawler->filter('.div-cell div div:nth-child(1) span:nth-child(1)')->text();

        try {
            $birthday = DateTimeParser::parseFromCzechDateString(
                $crawler->filter('.div-cell div div:nth-child(1) span:nth-child(3)')->text()
            );
        } catch (Throwable $exception) {
            $birthday = null;
        }

        $address = $crawler->filter('.div-cell div div:nth-child(2) span:nth-child(1)')->text();

        try {
            $registered = DateTimeParser::parseFromCzechDateString(
                s($crawler->text())->split(' zapsáno')[1]->trim()->split(' vymazáno')[0]->trim()
            );
        } catch (Throwable $exception) {
            $registered = null;
        }


        try {
            $deleted = DateTimeParser::parseFromCzechDateString(
                s($crawler->text())->split(' vymazáno')[1]->trim()
            );
        } catch (Throwable $exception) {
            $deleted = null;
        }

        $typeString = $crawler->filter('.vr-hlavicka')->text();
        $type = null;

        if (mb_strpos($typeString, 'ednatel') !== false) {
            $type = self::EXECUTIVE;
        }

        if (mb_strpos($typeString, 'polečník') !== false) {
            $type = self::PARTNER;
        }

        return new Person(
            $name,
            $birthday,
            $address,
            $registered,
            $deleted,
            $type,
        );
    }
}
