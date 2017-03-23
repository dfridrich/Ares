<?php

namespace Defr\Ares\Tests;

use Defr\Ares;
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
        if ($this->isTravis()) {
            $this->markTestSkipped('Travis cannot connect to Justice.cz');
        }

        $record = $this->ares->findByIdentificationNumber(27791394);
        $companyPeople
            = $record->getCompanyPeople();
        $this->assertCount(2, $companyPeople);
    }

    public function testBalancer()
    {
        $ares = new Ares();
        $ares->setBalancer('http://some.loadbalancer.domain');
        try {
            $ares->findByIdentificationNumber(26168685);
        } catch (Ares\AresException $e) {
        }
        $this->assertEquals('http://some.loadbalancer.domain?url=http%3A%2F%2Fwwwinfo.mfcr.cz%2Fcgi-bin%2Fares%2Fdarv_bas.cgi%3Fico%3D26168685', $ares->getLastUrl());
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
