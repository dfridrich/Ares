<?php

namespace Defr\Ares;

use Defr\Justice;
use Defr\ValueObject\Person;
use Goutte\Client as GouteClient;
use GuzzleHttp\Client as GuzzleClient;

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

    /**
     * AresRecord constructor.
     *
     * @param null $companyId
     * @param null $taxId
     * @param null $companyName
     * @param null $street
     * @param null $streetHouseNumber
     * @param null $streetOrientationNumber
     * @param null $town
     * @param null $area
     * @param null $zip
     */
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
        $stateCode = null
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
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param GouteClient $client
     *
     * @return $this
     */
    public function setClient(GouteClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return GouteClient
     */
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

    /**
     * @return array|Person[]
     */
    public function getCompanyPeople()
    {
        $justiceRecord = $this->getJusticeRecord();
        if ($justiceRecord) {
            return $justiceRecord->getPeople();
        }

        return [];
    }

    /**
     * @param int $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * @param string $taxId
     */
    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @param string $streetHouseNumber
     */
    public function setStreetHouseNumber($streetHouseNumber)
    {
        $this->streetHouseNumber = $streetHouseNumber;
    }

    /**
     * @param string $streetOrientationNumber
     */
    public function setStreetOrientationNumber($streetOrientationNumber)
    {
        $this->streetOrientationNumber = $streetOrientationNumber;
    }

    /**
     * @param string $town
     */
    public function setTown($town)
    {
        $this->town = $town;
    }

    /**
     * @param string $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return int
     */
    public function getStateCode()
    {
        return $this->stateCode;
    }

    /**
     * @param int $stateCode
     */
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
