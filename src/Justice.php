<?php

namespace Defr;

use Assert\Assertion;
use Defr\Justice\JusticeRecord;
use Defr\Justice\SubjectNotFoundException;
use Defr\Parser\JusticeJednatelPersonParser;
use Defr\Parser\JusticeSpolecnikPersonParser;
use Goutte\Client;
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

    /**
     * Justice constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param int $id
     *
     * @throws SubjectNotFoundException
     *
     * @return JusticeRecord|false
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

            try {
                if ('jednatel: ' === $title) {
                    $person = JusticeJednatelPersonParser::parseFromDomCrawler($table);
                    $people[$person->getName()] = $person;
                } elseif ('Společník: ' === $title) {
                    $person = JusticeSpolecnikPersonParser::parseFromDomCrawler($table);
                    $people[$person->getName()] = $person;
                }
            } catch (\Exception $e) {
            }
        });

        return new JusticeRecord($people);
    }

    /**
     * @param Crawler $crawler
     *
     * @return false|int
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
}
