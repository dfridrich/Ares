<?php

namespace Defr\Ares;

/**
 * Class AresRecord
 * @package Defr\Ares
 * @author Dennis Fridrich <fridrich.dennis@gmail.com>
 */
class AresRecord
{

    public $companyId;
    public $taxId;
    public $companyName;
    public $street;
    public $streetHouseNumber;
    public $streetOrientationNumber;
    public $town;
    public $zip;

    /**
     * @param $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * @param $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @param $taxId
     */
    public function setTaxId($taxId)
    {
        $this->taxId = !empty($taxId) ? $taxId : null;
    }

    /**
     * @param $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @param $streetHouseNumber
     */
    public function setStreetHouseNumber($streetHouseNumber)
    {
        $this->streetHouseNumber = !empty($streetHouseNumber) ? $streetHouseNumber : null;
    }

    /**
     * @param $streetOrientationNumber
     */
    public function setStreetOrientationNumber($streetOrientationNumber)
    {
        $this->streetOrientationNumber = !empty($streetOrientationNumber) ? $streetOrientationNumber : null;
    }

    /**
     * @return string
     */
    public function getStreetWithNumbers()
    {
        return $this->street . ' '
        . ($this->streetOrientationNumber ?
            $this->streetHouseNumber . '/' . $this->streetOrientationNumber :
            $this->streetHouseNumber);
    }

    /**
     * @param $town
     */
    public function setTown($town)
    {
        $this->town = $town;
    }

    /**
     * @param $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

}