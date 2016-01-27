<?php

namespace Defr\Tests;

use DateTimeInterface;
use Defr\Justice;
use Defr\Justice\JusticeRecord;
use Goutte\Client;
use PHPUnit_Framework_TestCase;

final class JusticeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Justice
     */
    private $justice;

    protected function setUp()
    {
        $this->justice = new Justice(new Client());
    }

    public function testFindById()
    {
        if (!$this->isJusticeOn()) {
            $this->markTestSkipped('Justice is down.');
        }

        $justiceRecord = $this->justice->findById(27791394);
        $this->assertInstanceOf(JusticeRecord::class, $justiceRecord);

        $people = $justiceRecord->getPeople();
        $this->assertCount(2, $people);

        $this->assertArrayHasKey('Mgr. ROBERT RUNTÁK', $people);
        $person = $people['Mgr. ROBERT RUNTÁK'];
        $this->assertInstanceOf(DateTimeInterface::class, $person->getBirthday());
        $this->assertInternalType('string', $person->getAddress());
    }

    public function testNotFoundFindId()
    {
        if (!$this->isJusticeOn()) {
            $this->markTestSkipped('Justice is down.');
        }

        $justiceRecord = $this->justice->findById(123456);
        $this->assertFalse($justiceRecord);
    }

    /**
     * @return bool
     */
    private function isJusticeOn()
    {
        try {
            (new Client())->request('GET', 'http://or.justice.cz');
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
