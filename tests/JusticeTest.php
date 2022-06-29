<?php

use Defr\Justice;
use Defr\Parser\PersonParser;
use Defr\ValueObject\Person;
use Goutte\Client;
use PHPUnit\Framework\TestCase;

final class JusticeTest extends TestCase
{
    /**
     * @var Justice
     */
    private $justice;

    protected function setUp(): void
    {
        if ($this->isTravis()) {
            $this->markTestSkipped('Travis cannot connect to Justice.cz');
        }

        $this->justice = new Justice(new Client());
    }

    public function testFindById(): void
    {
        $justiceRecord = $this->justice->findById(27791394);
        $this->assertInstanceOf('Defr\Justice\JusticeRecord', $justiceRecord);

        /** @var Person[] $people */
        $people = $justiceRecord->getPeople();
        $this->assertCount(3, $people);

        $this->assertSame($people[0]->getName(), 'DENNIS FRIDRICH');
        $this->assertEquals($people[0]->getBirthday(), new DateTimeImmutable('1985-08-09'));
        $this->assertEquals($people[0]->getRegistered(), new DateTimeImmutable('2017-07-03'));
        $this->assertSame($people[0]->getAddress(), 'Obděnice 15, 262 55 Petrovice');
        $this->assertNull($people[0]->getDeleted());
        $this->assertSame($people[0]->getType(), PersonParser::EXECUTIVE);

        $this->assertSame($people[1]->getName(), 'DENNIS FRIDRICH');
        $this->assertSame($people[2]->getName(), 'Mgr. ROBERT RUNTÁK');

        $this->assertFalse($justiceRecord->isInsolvencyRecord());
        $this->assertFalse($justiceRecord->isExecutionRecord());

        $justiceRecord = $this->justice->findById(26823357);
        $this->assertTrue($justiceRecord->isInsolvencyRecord());
        $this->assertFalse($justiceRecord->isExecutionRecord());
    }

    public function testNotFoundFindId(): void
    {
        $justiceRecord = $this->justice->findById(123456);
        $this->assertFalse($justiceRecord);
    }

    /**
     * @return bool
     */
    private function isTravis(): bool
    {
        if (getenv('TRAVIS_PHP_VERSION')) {
            return true;
        }

        return false;
    }
}
