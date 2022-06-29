<?php

namespace Defr\Ares;

use Defr\Justice;
use Goutte\Client as GouteClient;

/**
 * Class AresRecord.
 *
 * @author Dennis Fridrich <fridrich.dennis@gmail.com>
 */
class AresRecord
{
    /**
     * @var int
     */
    private $companyId;

    /**
     * @var string
     */
    private $taxId;

    /**
     * @var string
     */
    private $companyName;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $streetHouseNumber;

    /**
     * @var string
     */
    private $streetOrientationNumber;

    /**
     * @var string
     */
    private $town;

    /**
     * @var string
     */
    private $area;

    /**
     * @var string
     */
    private $zip;

    /**
     * @var integer
     */
    private $stateCode;

    /**
     * @var string
     */
    private $insolvencyRegister;

    /**
     * @var Justice
     */
    private $justiceRecord;

    /**
     * @var null|GouteClient
     */
    protected $client;

    public function __construct(
        $companyId = null,
        $taxId = null,
        $companyName = null,
        $street = null,
        $streetHouseNumber = null,
        $streetOrientationNumber = null,
        $town = null,
        $area = null,
        $zip = null,
        $stateCode = null,
        $insolvencyRegister = null
    ) {
        $this->companyId = $companyId;
        $this->taxId = !empty($taxId) ? $taxId : null;
        $this->companyName = $companyName;
        $this->street = $street;
        $this->streetHouseNumber = !empty($streetHouseNumber) ? $streetHouseNumber : null;
        $this->streetOrientationNumber = !empty($streetOrientationNumber) ? $streetOrientationNumber : null;
        $this->town = $town;
        $this->area = $area;
        $this->zip = $zip;
        $this->stateCode = $stateCode;
        $this->insolvencyRegister = $insolvencyRegister;
    }

    public function getStreetWithNumbers()
    {
        return $this->street.' '
            .($this->streetOrientationNumber
                ?
                $this->streetHouseNumber.'/'.$this->streetOrientationNumber
                :
                $this->streetHouseNumber);
    }

    public function __toString()
    {
        return $this->companyName;
    }

    public function getCompanyId()
    {
        return $this->companyId;
    }

    public function getTaxId()
    {
        return $this->taxId;
    }

    public function getCompanyName()
    {
        return $this->companyName;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function getStreetHouseNumber()
    {
        return $this->streetHouseNumber;
    }

    public function getStreetOrientationNumber()
    {
        return $this->streetOrientationNumber;
    }

    public function getTown()
    {
        return $this->town;
    }

    public function getArea()
    {
        return $this->area;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function setClient(GouteClient $client)
    {
        $this->client = $client;

        return $this;
    }

    public function getClient()
    {
        if (!$this->client) {
            $this->client = new GouteClient();
        }

        return $this->client;
    }

    public function getJusticeRecord()
    {
        if ($this->justiceRecord !== null) {
            return $this->justiceRecord;
        }
        $client = $this->getClient();
        $justice = new Justice($client);
        $this->justiceRecord = $justice->findById($this->companyId);
        return $this->justiceRecord;
    }

    public function getCompanyPeople($onlyActive = true)
    {
        $justiceRecord = $this->getJusticeRecord();
        if ($justiceRecord) {
            return $justiceRecord->getPeople($onlyActive);
        }

        return [];
    }

    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;
    }

    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function setStreetHouseNumber($streetHouseNumber)
    {
        $this->streetHouseNumber = $streetHouseNumber;
    }

    public function setStreetOrientationNumber($streetOrientationNumber)
    {
        $this->streetOrientationNumber = $streetOrientationNumber;
    }

    public function setTown($town)
    {
        $this->town = $town;
    }

    public function setArea($area)
    {
        $this->area = $area;
    }

    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    public function getStateCode()
    {
        return $this->stateCode;
    }

    public function setStateCode($stateCode)
    {
        $this->stateCode = $stateCode;
    }

    /**
     * @param string $registers
     *
     * N (nebo jiný znak) - není v evidenci
     * A - platná registrace
     * Z - zaniklá registrace
     * E - v pozici č. 22 označuje, že existuje záznam v Insolvenčním rejstříku. Nutno prověřit stav řízení!
     */
    public function setRegisters($registers)
    {
        if (isset($registers[21])) {
            $this->insolvencyRegister = $registers[21];
        }
    }

    public function getInsolvencyRegister()
    {
        return $this->insolvencyRegister;
    }
}
