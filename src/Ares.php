<?php

namespace Defr;

use Defr\Ares\ApiClient\ApiClient;
use Defr\Ares\AresException;
use Defr\Ares\AresRecord;
use Defr\Ares\AresRecords;
use InvalidArgumentException;

/**
 * Class Ares provides a way for retrieving data about business subjects from Czech Business register
 * (aka Obchodní rejstřík).
 *
 * @author Dennis Fridrich <fridrich.dennis@gmail.com>
 */
class Ares
{
	/** @deprecated no need to be public, will be removed in next major version */
    const URL_BAS = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=%s';
	/** @deprecated no need to be public, will be removed in next major version */
    const URL_RES = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_res.cgi?ICO=%s';
	/** @deprecated no need to be public, will be removed in next major version */
    const URL_TAX = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?ico=%s&filtr=0';
	/** @deprecated no need to be public, will be removed in next major version */
    const URL_FIND = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?obch_jm=%s&obec=%s&filtr=0';

	private ApiClient $apiClient;

    /**
     * @param string|null $cacheDir
     * @param bool        $debug
     * @param string|null $balancer
     */
    public function __construct($cacheDir = null, $debug = false, $balancer = null)
    {
		$this->apiClient = new ApiClient($cacheDir, $debug, $balancer);
    }

    /**
     * @param mixed $id
     *
     * @throws InvalidArgumentException
     * @throws Ares\AresException
     *
     * @return AresRecord
     */
    public function findByIdentificationNumber($id)
    {
        $this->checkCompanyId($id);

        if (empty($id)) {
            throw new AresException('IČ firmy musí být zadáno.');
        }

        return $this->apiClient->findByIdentificationNumber($id);
    }

    /**
     * Find subject in RES (Registr ekonomických subjektů)
     *
     * @param $id
     *
     * @throws InvalidArgumentException
     * @throws Ares\AresException
     *
     * @return AresRecord
     */
    public function findInResById($id)
    {
        $this->checkCompanyId($id);

        return $this->apiClient->findInResById($id);
    }

    /**
     * Find subject's tax ID in OR (Obchodní rejstřík).

     * @param $id
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     *
     * @return AresRecord
     */
    public function findVatById($id)
    {
        $this->checkCompanyId($id);

        return $this->apiClient->findVatById($id);
    }

    /**
     * Find subject by its name in OR (Obchodní rejstřík).
     *
     * @param string      $name
     * @param string|null $city
     *
     * @throws InvalidArgumentException
     * @throws AresException
     *
     * @return array|AresRecord[]|AresRecords
     */
    public function findByName($name, $city = null)
    {
        if (strlen($name) < 3) {
            throw new InvalidArgumentException('Zadejte minimálně 3 znaky pro hledání.');
        }

		return $this->apiClient->findByName($name, $city);
    }

    /**
     * @param string $cacheStrategy
     */
    public function setCacheStrategy($cacheStrategy)
    {
		$this->apiClient->setCacheStrategy($cacheStrategy);
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug)
    {
		$this->apiClient->setDebug($debug);
    }

    /**
     * @param string $balancer
     *
     * @return $this
     */
    public function setBalancer($balancer)
    {
		$this->apiClient->setBalancer($balancer);

        return $this;
    }

    /**
     * @return string
     */
    public function getLastUrl()
    {
        return $this->apiClient->getLastUrl();
    }

    /**
     * @param mixed $id
     */
    private function checkCompanyId($id)
    {
        if (!is_numeric($id) || !preg_match('/^\d+$/', $id)) {
            throw new InvalidArgumentException('IČ firmy musí být číslo.');
        }
    }

}
