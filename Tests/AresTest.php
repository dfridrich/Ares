<?php

namespace Defr\Ares\Tests;

use Defr\Ares;
use Goutte\Client;
use PHPUnit_Framework_TestCase;

final class AresTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Ares
     */
    private $ares;

    protected function setUp()
    {
        $this->ares = new Ares();
    }

    public function testFindByIdentificationNumber()
    {
        $record = $this->ares->findByIdentificationNumber(73263753);

        $this->assertSame('Dennis Fridrich', $record->getCompanyName());
        $this->assertSame('CZ8508095453', $record->getTaxId());
        $this->assertSame(73263753, $record->getCompanyId());
        $this->assertSame('Herodova', $record->getStreet());
        $this->assertSame('1871', $record->getStreetHouseNumber());
        $this->assertSame('4', $record->getStreetOrientationNumber());
        $this->assertSame('Ostrava - Moravská Ostrava', $record->getTown());
        $this->assertSame('70200', $record->getZip());
    }

    /**
     * @expectedException \Defr\Ares\AresException
     */
    public function testFindByIdentificationNumberException()
    {
        $this->ares->findByIdentificationNumber('A1234');
    }

    public function testGetCompanyPeople()
    {
        if (!$this->isJusticeOn()) {
            $this->markTestSkipped('Justice is down.');
        }

        $record = $this->ares->findByIdentificationNumber(27791394);

        $companyPeople = $record->getCompanyPeople();
        $this->assertCount(2, $companyPeople);
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
