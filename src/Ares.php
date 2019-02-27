<?php

namespace Defr;

use Defr\Ares\AresException;
use Defr\Ares\AresRecord;
use Defr\Ares\AresRecords;
use Defr\Ares\TaxRecord;
use InvalidArgumentException;

/**
 * Class Ares provides a way for retrieving data about business subjects from Czech Business register
 * (aka Obchodní rejstřík).
 *
 * @author Dennis Fridrich <fridrich.dennis@gmail.com>
 */
class Ares
{
    const URL_BAS = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=%s';
    const URL_RES = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_res.cgi?ICO=%s';
    const URL_TAX = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?ico=%s&filtr=0';
    const URL_FIND = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?obch_jm=%s&obec=%s&filtr=0';

    /**
     * @var string
     */
    private $cacheStrategy = 'YW';

    /**
     * Path to directory that serves as an local cache for Ares service responses.
     *
     * @var string
     */
    private $cacheDir = null;

    /**
     * Whether we are running in debug/development environment.
     *
     * @var bool
     */
    private $debug;

    /**
     * Load balancing domain URL.
     *
     * @var string
     */
    private $balancer = null;

    /**
     * Stream context options.
     *
     * @var array
     */
    private $contextOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];

    /**
     * Last called URL.
     *
     * @var string
     */
    private $lastUrl;

    /**
     * @param string|null $cacheDir
     * @param bool        $debug
     * @param string|null $balancer
     */
    public function __construct($cacheDir = null, $debug = false, $balancer = null)
    {
        if (null === $cacheDir) {
            $cacheDir = sys_get_temp_dir();
        }

        if (null !== $balancer) {
            $this->balancer = $balancer;
        }

        $this->cacheDir = $cacheDir.'/defr/ares';
        $this->debug = $debug;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     * @param $id
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

        $cachedFileName = $id.'_'.date($this->cacheStrategy).'.php';
        $cachedFile = $this->cacheDir.'/bas_'.$cachedFileName;
        $cachedRawFile = $this->cacheDir.'/bas_raw_'.$cachedFileName;

        if (is_file($cachedFile)) {
            return unserialize(file_get_contents($cachedFile));
        }

        $url = $this->composeUrl(sprintf(self::URL_BAS, $id));

        try {
            $aresRequest = file_get_contents($url, false, stream_context_create($this->contextOptions));
            if ($this->debug) {
                file_put_contents($cachedRawFile, $aresRequest);
            }
            $aresResponse = simplexml_load_string($aresRequest);

            if ($aresResponse) {
                $ns = $aresResponse->getDocNamespaces();
                $data = $aresResponse->children($ns['are']);
                $elements = $data->children($ns['D'])->VBAS;

                $ico = $elements->ICO;
                if ((string) $ico !== (string) $id) {
                    throw new AresException('IČ firmy nebylo nalezeno.');
                }

                $record = new AresRecord();

                $record->setCompanyId(strval($elements->ICO));
                $record->setTaxId(strval($elements->DIC));
                $record->setCompanyName(strval($elements->OF));
                if (strval($elements->AA->NU) !== '') {
                    $record->setStreet(strval($elements->AA->NU));
                } else {
                    if (strval($elements->AA->NCO) !== '') {
                        $record->setStreet(strval($elements->AA->NCO));
                    }
                }

                if (strval($elements->AA->CO)) {
                    $record->setStreetHouseNumber(strval($elements->AA->CD));
                    $record->setStreetOrientationNumber(strval($elements->AA->CO));
                } else {
                    $record->setStreetHouseNumber(strval($elements->AA->CD));
                }

                if (strval($elements->AA->N) === 'Praha') {
                    $record->setTown(strval($elements->AA->NMC));
                    $record->setArea(strval($elements->AA->NCO));
                } elseif (strval($elements->AA->NCO) !== strval($elements->AA->N)) {
                    $record->setTown(strval($elements->AA->N));
                    $record->setArea(strval($elements->AA->NCO));
                } else {
                    $record->setTown(strval($elements->AA->N));
                }

                $record->setZip(strval($elements->AA->PSC));

                $record->setRegisters(strval($elements->PSU));

                $stateInfo = $elements->AA->AU->children($ns['U']);
                $record->setStateCode(strval($stateInfo->KK));
            } else {
                throw new AresException('Databáze ARES není dostupná.');
            }
        } catch (\Exception $e) {
            throw new AresException($e->getMessage());
        }

        file_put_contents($cachedFile, serialize($record));

        return $record;
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

        $url = $this->composeUrl(sprintf(self::URL_RES, $id));

        $cachedFileName = $id.'_'.date($this->cacheStrategy).'.php';
        $cachedFile = $this->cacheDir.'/res_'.$cachedFileName;
        $cachedRawFile = $this->cacheDir.'/res_raw_'.$cachedFileName;

        if (is_file($cachedFile)) {
            return unserialize(file_get_contents($cachedFile));
        }

        try {
            $aresRequest = file_get_contents($url, false, stream_context_create($this->contextOptions));
            if ($this->debug) {
                file_put_contents($cachedRawFile, $aresRequest);
            }
            $aresResponse = simplexml_load_string($aresRequest);

            if ($aresResponse) {
                $ns = $aresResponse->getDocNamespaces();
                $data = $aresResponse->children($ns['are']);
                $elements = $data->children($ns['D'])->Vypis_RES;

                if (strval($elements->ZAU->ICO) === $id) {
                    $record = new AresRecord();
                    $record->setCompanyId(strval($id));
                    $record->setTaxId($this->findVatById($id));
                    $record->setCompanyName(strval($elements->ZAU->OF));
                    $record->setStreet(strval($elements->SI->NU));
                    $record->setStreetHouseNumber(strval($elements->SI->CD));
                    $record->setStreetOrientationNumber(strval($elements->SI->CO));
                    $record->setTown(strval($elements->SI->N));
                    $record->setZip(strval($elements->SI->PSC));
                } else {
                    throw new AresException('IČ firmy nebylo nalezeno.');
                }
            } else {
                throw new AresException('Databáze ARES není dostupná.');
            }
        } catch (\Exception $e) {
            throw new AresException($e->getMessage());
        }
        file_put_contents($cachedFile, serialize($record));

        return $record;
    }

    /**
     * Find subject's tax ID in OR (Obchodní rejstřík).

     * @param $id
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     *
     * @return string
     */
    public function findVatById($id)
    {
        $this->checkCompanyId($id);

        $url = $this->composeUrl(sprintf(self::URL_TAX, $id));

        $cachedFileName = $id.'_'.date($this->cacheStrategy).'.php';
        $cachedFile = $this->cacheDir.'/tax_'.$cachedFileName;
        $cachedRawFile = $this->cacheDir.'/tax_raw_'.$cachedFileName;

        if (is_file($cachedFile)) {
            return unserialize(file_get_contents($cachedFile));
        }

        try {
            $vatRequest = file_get_contents($url, false, stream_context_create($this->contextOptions));
            if ($this->debug) {
                file_put_contents($cachedRawFile, $vatRequest);
            }
            $vatResponse = simplexml_load_string($vatRequest);

            if ($vatResponse) {
                $record = new TaxRecord();
                $ns = $vatResponse->getDocNamespaces();
                $data = $vatResponse->children($ns['are']);
                $elements = $data->children($ns['dtt'])->V->S;

                if (strval($elements->ico) === $id) {
                    $record->setTaxId(str_replace('dic=', 'CZ', strval($elements->p_dph)));
                } else {
                    throw new AresException('DIČ firmy nebylo nalezeno.');
                }
            } else {
                throw new AresException('Databáze MFČR není dostupná.');
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        file_put_contents($cachedFile, serialize($record));

        return $record;
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

        $url = $this->composeUrl(sprintf(
            self::URL_FIND,
            urlencode(Lib::stripDiacritics($name)),
            urlencode(Lib::stripDiacritics($city))
        ));

        $cachedFileName = date($this->cacheStrategy).'_'.md5($name.$city).'.php';
        $cachedFile = $this->cacheDir.'/find_'.$cachedFileName;
        $cachedRawFile = $this->cacheDir.'/find_raw_'.$cachedFileName;

        if (is_file($cachedFile)) {
            return unserialize(file_get_contents($cachedFile));
        }

        $aresRequest = file_get_contents($url, false, stream_context_create($this->contextOptions));
        if ($this->debug) {
            file_put_contents($cachedRawFile, $aresRequest);
        }
        $aresResponse = simplexml_load_string($aresRequest);
        if (!$aresResponse) {
            throw new AresException('Databáze ARES není dostupná.');
        }

        $ns = $aresResponse->getDocNamespaces();
        $data = $aresResponse->children($ns['are']);
        $elements = $data->children($ns['dtt'])->V->S;

        if (empty($elements)) {
            throw new AresException('Nic nebylo nalezeno.');
        }

        $records = new AresRecords();
        foreach ($elements as $element) {
            $record = new AresRecord();
            $record->setCompanyId(strval($element->ico));
            $record->setTaxId(
                ($element->dph ? str_replace('dic=', 'CZ', strval($element->p_dph)) : '')
            );
            $record->setCompanyName(strval($element->ojm));
            $records[] = $record;
        }
        file_put_contents($cachedFile, serialize($records));

        return $records;
    }

    /**
     * @param string $cacheStrategy
     */
    public function setCacheStrategy($cacheStrategy)
    {
        $this->cacheStrategy = $cacheStrategy;
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param string $balancer
     *
     * @return $this
     */
    public function setBalancer($balancer)
    {
        $this->balancer = $balancer;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastUrl()
    {
        return $this->lastUrl;
    }

    /**
     * @param int $id
     */
    private function checkCompanyId($id)
    {
        if (!is_numeric($id) || !preg_match('/^\d+$/', $id)) {
            throw new InvalidArgumentException('IČ firmy musí být číslo.');
        }
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function composeUrl($url)
    {
        if ($this->balancer) {
            $url = sprintf('%s?url=%s', $this->balancer, urlencode($url));
        }

        $this->lastUrl = $url;

        return $url;
    }

}
