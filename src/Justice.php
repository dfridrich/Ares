<?php

namespace Defr;

use Assert\Assertion;
use Defr\Justice\JusticeRecord;
use Defr\Justice\SubjectNotFoundException;
use Defr\Parser\DateTimeParser;
use Defr\Parser\JusticeJednatelPersonParser;
use Defr\Parser\JusticeSpolecnikPersonParser;
use Defr\ValueObject\Person;
use Goutte\Client;
use Nette\Utils\DateTime;
use Symfony\Component\DomCrawler\Crawler;

final class Justice
{
    /**
     * @var string
     */
    const URL_BASE = 'https://or.justice.cz/ias/ui/';

    /**
     * @var string
     */
    const URL_SUBJECTS = 'https://or.justice.cz/ias/ui/rejstrik-$firma?ico=%d';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param int $id
     *
     * @return JusticeRecord|false
     *
     * @throws SubjectNotFoundException
     */
    public function findById($id)
    {
        Assertion::integer($id);

        $crawler = $this->client->request('GET', sprintf(self::URL_SUBJECTS, $id));
        $detailUrl = $this->extractDetailUrlFromCrawler($crawler);

        if (false === $detailUrl) {
            return false;
        }

        $people = [];

        $crawler = $this->client->request('GET', $detailUrl);
        $crawler->filter('.aunp-content .div-table')->each(function (Crawler $table) use (&$people) {
            $title = $table->filter('.vr-hlavicka')->text();

            if ('jednatel: ' === $title) {
                $person = JusticeJednatelPersonParser::parseFromDomCrawler($table);
                $people[$person->getName()] = $person;
            } elseif ('Společník: ' === $title) {
                $person = JusticeSpolecnikPersonParser::parseFromDomCrawler($table);
                $people[$person->getName()] = $person;
            }
        });

        return new JusticeRecord($people);
    }

    /**
     * @return int|false
     */
    private function extractDetailUrlFromCrawler(Crawler $crawler)
    {
        $linksFound = $crawler->filter('.result-links > li > a');
        if (!$linksFound) {
            return false;
        }

        $href = $linksFound->extract(['href']);
        if (!isset($href[1])) {
            return false;
        }

        return self::URL_BASE.$href[1];
    }

//    /**
//     * @return Person
//     */
//    private function parseJednatel(Crawler $table)
//    {
//        $content = $table->filter('.div-cell div div')->text();
//        $content = $this->removeEmptyLines($content);
//
//        $contentItems = explode(PHP_EOL, $content);
//        $name = trim(explode(',', $contentItems[0])[0]);
//        $birthday = DateTimeParser::parseFromCzechDateString($contentItems[1]);
//        $address = trim($contentItems[2]);
//
//        return new Person($name, $birthday, $address);
//    }


}
