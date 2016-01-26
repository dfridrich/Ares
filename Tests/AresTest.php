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

        $this->assertEquals('Dennis Fridrich', $record->getCompanyName());
        $this->assertEquals('CZ8508095453', $record->getTaxId());
    }

    /**
     * @expectedException \Defr\Ares\AresException
     */
    public function testFindByIdentificationNumberException()
    {
        $this->ares->findByIdentificationNumber('A1234');
    }

//    public function testGetCompanyPeople()
//    {
//        $record = $this->ares->findByIdentificationNumber(27791394); // SevenDesign IČ
//
//        $companyPeople = $record->getCompanyPeople();
//        $this->assertCount(2, $companyPeople);
//    }
}
