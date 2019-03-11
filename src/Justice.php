<?php

namespace Defr;

use Assert\Assertion;
use Defr\Justice\JusticeRecord;
use Defr\Justice\SubjectNotFoundException;
use Defr\Parser\PersonParser;
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
     * @throws \Assert\AssertionFailedException
     *
     * @return JusticeRecord|false
     */
    public function findById($id)
    {

        $id = is_numeric($id) ? (int)$id : $id;

        Assertion::integer($id);

        $crawler = $this->client->request('GET', sprintf(self::URL_SUBJECTS, $id));
        $detailUrl = $this->extractDetailUrlFromCrawler($crawler);

        if (false === $detailUrl) {
            return false;
        }

        $people = [];

        $justice = new JusticeRecord();

        $crawler = $this->client->request('GET', $detailUrl);
        $crawler->filter('.aunp-content .div-table')->each(function (Crawler $table) use (&$people, &$justice) {
            $title = trim($table->filter('.vr-hlavicka')->text());

            try {
                if (in_array($title, ['jednatel:', 'Jednatel:', 'Společník:'])) {
                    $person = PersonParser::parseFromDomCrawler($table);
                    if ($person !== null && !isset($people[$person->getName()])) {
                        $people[$person->getName()] = $person;
                    }
                }

                if ($justice->isInsolvencyRecord() !== true && $title === 'Údaje o insolvencích:') {
                    $justice->setInsolvencyRecord(true);
                }
                if ($justice->isExecutionRecord() !== true && $title === 'Údaje o exekucích:') {
                    $justice->setExecutionRecord(true);
                }
            } catch (\Exception $e) {
                throw $e;
            }
        });

        $justice->setPeople($people);

        return $justice;
    }

    /**
     * @param Crawler $crawler
     *
     * @return false|string
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
