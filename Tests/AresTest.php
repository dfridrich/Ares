<?php

use Defr\Ares;

class AresTest extends PHPUnit_Framework_TestCase
{
    public function testFindByIdentificationNumber()
    {
        $ares = new Ares();
        $record = $ares->findByIdentificationNumber(73263753);

        $this->assertEquals('Dennis Fridrich', $record->getCompanyName());
        $this->assertEquals('CZ8508095453', $record->getTaxId());
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
