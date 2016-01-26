<?php

use Defr\Ares;

class AresTest extends PHPUnit_Framework_TestCase
{
    public function testFindByIdentificationNumber()
    {
        $ares = new Ares();
        $record = $ares->findByIdentificationNumber(73263753);

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
     * @expectedException Defr\Ares\AresException
     */
    public function testFindByIdentificationNumberException()
    {
        $ares = new Ares();
        $ares->findByIdentificationNumber('A1234');
    }
}
