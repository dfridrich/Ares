<?php

namespace Defr\Tests;

use Defr\Justice;
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
        if ($this->isTravis()) {
            $this->markTestSkipped('Travis cannot connect to Justice.cz');
        }

        $this->justice = new Justice(new Client());
    }

    public function testFindById()
    {
        $justiceRecord = $this->justice->findById(27791394);
        $this->assertInstanceOf('Defr\Justice\JusticeRecord', $justiceRecord);

        $people = $justiceRecord->getPeople();
        $this->assertCount(2, $people);

        $this->assertArrayHasKey('Mgr. Robert Runták', $people);
        $person = $people['Mgr. Robert Runták'];
        $this->assertInstanceOf('DateTime', $person->getBirthday());
        $this->assertInternalType('string', $person->getAddress());

        $this->assertFalse($justiceRecord->isInsolvencyRecord());
        $this->assertFalse($justiceRecord->isExecutionRecord());

        $justiceRecord = $this->justice->findById(28962788);
        $this->assertFalse($justiceRecord->isInsolvencyRecord());
        $this->assertTrue($justiceRecord->isExecutionRecord());

        $justiceRecord = $this->justice->findById(26823357);
        $this->assertTrue($justiceRecord->isInsolvencyRecord());
        $this->assertFalse($justiceRecord->isExecutionRecord());
    }

    public function testNotFoundFindId()
    {
        $justiceRecord = $this->justice->findById(123456);
        $this->assertFalse($justiceRecord);
    }

    /**
     * @return bool
     */
    private function isTravis()
    {
        if (getenv('TRAVIS_PHP_VERSION')) {
            return true;
        }

        return false;
    }
}
