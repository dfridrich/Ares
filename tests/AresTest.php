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
        $this->ares = new Ares(null,true);
    }

    public function testFindByIdentificationNumber()
    {
        $record = $this->ares->findByIdentificationNumber(73263753);
        $this->assertSame('Dennis Fridrich', $record->getCompanyName());
        $this->assertSame('', $record->getTaxId());
        $this->assertSame('73263753', $record->getCompanyId());
        $this->assertSame('Obděnice', $record->getStreet());
        $this->assertSame('15', $record->getStreetHouseNumber());
        $this->assertEmpty($record->getStreetOrientationNumber());
        $this->assertSame('Obděnice 15', $record->getStreetWithNumbers());
        $this->assertSame('Petrovice', $record->getTown());
        $this->assertSame('26255', $record->getZip());
        $this->assertSame('27', $record->getStateCode());
        $record = $this->ares->findByIdentificationNumber(48528803);
        $this->assertSame('Milady Horákové 890/20', $record->getStreetWithNumbers());
        $record = $this->ares->findByIdentificationNumber(1313410);
        $this->assertSame('Opatovice 76', $record->getStreetWithNumbers());
    }

    public function testFindByIdentificationNumberWithLeadingZeros()
    {
        $record = $this->ares->findByIdentificationNumber('00006947');
        $this->assertSame('00006947', $record->getCompanyId());
    }

    /**
     * @expectedException \Defr\Ares\AresException
     */
    public function testFindByIdentificationNumberException()
    {
        $this->ares->findByIdentificationNumber('A1234');
    }

    /**
     * @expectedException \Defr\Ares\AresException
     */
    public function testFindByEmptyStringException()
    {
        $this->ares->findByIdentificationNumber('');
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

//    public function testBalancer()
//    {
//        $ares = new Ares();
//        $ares->setBalancer('http://some.loadbalancer.domain');
//        try {
//            $ares->findByIdentificationNumber(26168685);
//        } catch (Ares\AresException $e) {
//            throw $e;
//        }
//        $this->assertEquals(
//            'http://some.loadbalancer.domain'
//            .'?url=http%3A%2F%2Fwwwinfo.mfcr.cz%2Fcgi-bin%2Fares%2Fdarv_bas.cgi%3Fico%3D26168685',
//            $ares->getLastUrl()
//        );
//    }

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
