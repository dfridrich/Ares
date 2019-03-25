<?php

namespace Defr\Parser;

use Defr\Parser\Helper\StringHelper;
use Defr\ValueObject\Person;
use Symfony\Component\DomCrawler\Crawler;

final class PersonParser
{
    /**
     * @param Crawler $crawler
     *
     * @return Person
     */
    public static function parseFromDomCrawler(Crawler $crawler)
    {

        $person = $crawler->filter('.div-cell')->each(function (Crawler $node, $i) {
            $content = StringHelper::removeEmptyLines($node->text());

            $contentItems = explode("\n", $content);
            $contentItems = array_map('trim', $contentItems);

            if ($i === 0) {
                if (mb_strpos($contentItems[0], 'ednatel') !== false) {
                    return ['type' => 'executive'];
                }
                return ['type' => 'partner'];
            }

            if (count($contentItems)) {
                if (mb_strpos($contentItems[0], ', dat. nar.') !== false) {

                    $name = trim(explode(', dat. nar.', $contentItems[0])[0]);

                    try {
                        $birthday = DateTimeParser::parseFromCzechDateString($contentItems[1]);
                        $address = $contentItems[2];
                    } catch (\Exception $e) {
                        $birthday = null;
                    }

                    return ['name' => ucwords(mb_strtolower($name)), 'birthday' => $birthday, 'address' => isset($address) ? $address : ''];
                }

                foreach ($contentItems as $item) {
                    if (mb_strpos($item, 'zapsáno') !== false) {
                        $registered = DateTimeParser::parseFromCzechDateString(mb_substr($item, 8));
                    }
                    if (mb_strpos($item, 'vymazáno') !== false) {
                        $deleted = DateTimeParser::parseFromCzechDateString(mb_substr($item, 9));
                    }
                }

                if (isset($registered) && !isset($deleted)) {
                    return ['registered' => $registered];
                }
            }

            return null;

        });

        if (isset($person[2]['birthday']) && isset($person[3]['registered'])) {
            return new Person($person[2]['name'], $person[2]['birthday'], $person[2]['address'], $person[3]['registered'], $person[0]['type']);
        }

        if (isset($person[1]['birthday']) && isset($person[2]['registered'])) {
            return new Person($person[1]['name'], $person[1]['birthday'], $person[1]['address'], $person[2]['registered'], $person[0]['type']);
        }

        return null;
    }
}
