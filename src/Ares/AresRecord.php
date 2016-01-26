<?php

namespace Defr\Ares;

use Defr\Justice;
use Goutte\Client;

/**
 * Class AresRecord.
 *
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
        return $this->street.' '
        .($this->streetOrientationNumber
            ?
            $this->streetHouseNumber.'/'.$this->streetOrientationNumber
            :
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

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->companyName;
    }

    /**
     * @return mixed
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @return mixed
     */
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return mixed
     */
    public function getStreetHouseNumber()
    {
        return $this->streetHouseNumber;
    }

    /**
     * @return mixed
     */
    public function getStreetOrientationNumber()
    {
        return $this->streetOrientationNumber;
    }

    /**
     * @return mixed
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @return array
     */
    public function getCompanyPeople()
    {
        $justice = new Justice(new Client());
        $justiceRecord = $justice->findById($this->companyId);
        if ($justiceRecord) {
            return $justiceRecord->getPeople();
        }

        return [];
    }
}
