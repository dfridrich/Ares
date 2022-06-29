<?php

use Defr\Justice;
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

        $people = $justiceRecord->getPeople();
        $this->assertCount(2, $people);

        $this->assertArrayHasKey('Mgr. ROBERT RUNTÁK', $people);
        $person = $people['Mgr. ROBERT RUNTÁK'];
        $this->assertInstanceOf('DateTime', $person->getBirthday());
        $this->assertIsString($person->getAddress());
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
