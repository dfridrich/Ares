<?php

namespace Defr;

use Defr\Ares\AresException;
use Defr\Ares\AresRecord;
use Defr\Ares\AresRecords;
use Defr\Ares\TaxRecord;

/**
 * Class Ares
 * @package Defr
 * @author Dennis Fridrich <fridrich.dennis@gmail.com>
 */
class Ares
{

    const URL_BAS = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=%s';
    const URL_RES = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_res.cgi?ICO=%s';
    const URL_TAX = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?ico=%s';
    const URL_FIND = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?obch_jm=%s&obec=%s';

    /**
     * @var string
     */
    private $cacheStrategy = 'YW';

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @param $cacheDir
     * @param bool $debug
     */
    public function __construct($cacheDir, $debug = false)
    {

        $this->cacheDir = $cacheDir . '/defr/ares';
        $this->debug = $debug;
        // Create cache dirs if they dont exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

    }

    /**
     * @param $id
     * @return AresRecord
     * @throws \InvalidArgumentException
     * @throws Ares\AresException
     */
    public function findByIdentificationNumber($id)
    {

        $id = Lib::toInteger($id);

        if (!is_int($id)) {
            throw new \InvalidArgumentException('IČ firmy musí být číslo.');
        }

        // Sestaveni URL
        $url = sprintf(self::URL_BAS, $id);

        $cachedFileName = $id . '_' . date($this->cacheStrategy) . '.php';
        $cachedFile = $this->cacheDir . '/bas_' . $cachedFileName;
        $cachedRawFile = $this->cacheDir . '/bas_raw_' . $cachedFileName;

        if (!is_file($cachedFile)) {

            try {

                $aresRequest = file_get_contents($url);
                if ($this->debug) {
                    file_put_contents($cachedRawFile, $aresRequest);
                }
                $aresResponse = simplexml_load_string($aresRequest);

                if ($aresResponse) {
                    $ns = $aresResponse->getDocNamespaces();
                    $data = $aresResponse->children($ns['are']);
                    $elements = $data->children($ns['D'])->VBAS;

                    if (strval($elements->ICO) == $id) {

                        $record = new AresRecord();

                        $record->setCompanyId($id);
                        $record->setTaxId(strval($elements->DIC));
                        $record->setCompanyName(strval($elements->OF));
                        $record->setStreet(strval($elements->AA->NU));

                        if (strval($elements->AA->CO)) {
                            $record->setStreetHouseNumber(strval($elements->AA->CD));
                            $record->setStreetOrientationNumber(strval($elements->AA->CO));
                        } else {
                            $record->setStreetHouseNumber(strval($elements->AA->CD));
                        }

                        if (strval($elements->AA->NCO)) {
                            $record->setTown(strval($elements->AA->N . ' - ' . strval($elements->AA->NCO)));
                        } else {
                            $record->setTown(strval($elements->AA->N));
                        }

                        $record->setZip(strval($elements->AA->PSC));

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

        } else {

            /** @var AresRecord $record */
            $record = unserialize(file_get_contents($cachedFile));

        }

        return $record;

    }

    /**
     * @param $id
     * @return AresRecord
     * @throws \InvalidArgumentException
     * @throws Ares\AresException
     */
    public function findInResById($id)
    {

        $id = Lib::toInteger($id);

        if (!is_int($id)) {
            throw new \InvalidArgumentException('IČ firmy musí být číslo.');
        }

        // Sestaveni URL
        $url = sprintf(self::URL_RES, $id);

        $cachedFileName = $id . '_' . date($this->cacheStrategy) . '.php';
        $cachedFile = $this->cacheDir . '/res_' . $cachedFileName;
        $cachedRawFile = $this->cacheDir . '/res_raw_' . $cachedFileName;

        if (!is_file($cachedFile)) {
            try {
                $aresRequest = file_get_contents($url);
                file_put_contents($cachedRawFile, $aresRequest);
                $aresResponse = simplexml_load_string($aresRequest);

                if ($aresResponse) {
                    $ns = $aresResponse->getDocNamespaces();
                    $data = $aresResponse->children($ns['are']);
                    $elements = $data->children($ns['D'])->Vypis_RES;

                    if (strval($elements->ZAU->ICO) == $id) {
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
        } else {
            /** @var AresRecord $record */
            $record = unserialize(file_get_contents($cachedFile));
        }

        return $record;

    }

    /**
     * @param $id
     * @return TaxRecord|mixed
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function findVatById($id)
    {

        $id = Lib::toInteger($id);

        if (!is_int($id)) {
            throw new \InvalidArgumentException('IČ firmy musí být číslo.');
        }

        // Sestaveni URL
        $url = sprintf(self::URL_TAX, $id);

        $cachedFileName = $id . '_' . date($this->cacheStrategy) . '.php';
        $cachedFile = $this->cacheDir . '/tax_' . $cachedFileName;
        $cachedRawFile = $this->cacheDir . '/tax_raw_' . $cachedFileName;

        if (!is_file($cachedFile)) {
            try {
                $vatRequest = file_get_contents($url);
                file_put_contents($cachedRawFile, $vatRequest);
                $vatResponse = simplexml_load_string($vatRequest);

                if ($vatResponse) {
                    $record = new TaxRecord();
                    $ns = $vatResponse->getDocNamespaces();
                    $data = $vatResponse->children($ns['are']);
                    $elements = $data->children($ns['dtt'])->V->S;

                    if (strval($elements->ico) == $id) {
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
        } else {
            $record = unserialize(file_get_contents($cachedFile));
        }

        return $record;

    }

    /**
     * @param $name
     * @param null $city
     * @return array|AresRecords
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function findByName($name, $city = null)
    {

        if (strlen($name) < 3) {
            throw new \InvalidArgumentException('Zadejte minimálně tři znaky pro hledání.');
        }

        $url = sprintf(
            self::URL_FIND,
            urlencode(Lib::stripDiacritics($name)),
            urlencode(Lib::stripDiacritics($city))
        );

        $cachedFileName = date($this->cacheStrategy) . '_' . md5($name . $city) . '.php';
        $cachedFile = $this->cacheDir . '/find_' . $cachedFileName;
        $cachedRawFile = $this->cacheDir . '/find_raw_' . $cachedFileName;

        if (!is_file($cachedFile)) {
            try {
                $aresRequest = file_get_contents($url);
                file_put_contents($cachedRawFile, $aresRequest);
                $aresResponse = simplexml_load_string($aresRequest);

                if ($aresResponse) {
                    $ns = $aresResponse->getDocNamespaces();
                    $data = $aresResponse->children($ns['are']);
                    $elements = $data->children($ns['dtt'])->V->S;

                    if (!count($elements)) {
                        throw new AresException('Nic nebylo nalezeno.');
                    } else {
                        $records = new AresRecords();
                        foreach ($elements as $element) {
                            $record = new AresRecord();
                            $record->setCompanyId(strval($element->ico));
                            $record->setTaxId(
                                ($element->dph ? str_replace('dic=', 'CZ', strval($element->p_dph)) : '')
                            );
                            $record->setCompanyName(strval($element->ojm));
                            //'adresa' => strval($element->jmn));
                            $records[] = $record;
                        }
                    }
                } else {
                    throw new AresException('Databáze ARES není dostupná.');
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
            file_put_contents($cachedFile, serialize($records));
        } else {
            $records = unserialize(file_get_contents($cachedFile));
        }

        return $records;

    }

}