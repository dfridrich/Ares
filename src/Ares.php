<?php

namespace Defr;

use Defr\Ares\AresException;
use Defr\Ares\AresRecord;
use Defr\Ares\AresRecords;
use Defr\Ares\TaxRecord;
use InvalidArgumentException;

/**
 * Class Ares.
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
     * @var string
     */
    private $cacheDir = null;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var array
     */
    private $contextOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];

    /**
     * @param null $cacheDir
     * @param bool $debug
     */
    public function __construct($cacheDir = null, $debug = false)
    {
        if ($cacheDir === null) {
            $cacheDir = sys_get_temp_dir();
        }

        $this->cacheDir = $cacheDir.'/defr/ares';
        $this->debug = $debug;

        // Create cache dirs if they doesn't exist
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
        $id = Lib::toInteger($id);
        $this->ensureIdIsInteger($id);

        $cachedFileName = $id.'_'.date($this->cacheStrategy).'.php';
        $cachedFile = $this->cacheDir.'/bas_'.$cachedFileName;
        $cachedRawFile = $this->cacheDir.'/bas_raw_'.$cachedFileName;

        if (is_file($cachedFile)) {
            return unserialize(file_get_contents($cachedFile));
        }

        // Sestaveni URL
        $url = sprintf(self::URL_BAS, $id);

        try {
            $aresRequest = file_get_contents($url, null, stream_context_create($this->contextOptions));
            if ($this->debug) {
                file_put_contents($cachedRawFile, $aresRequest);
            }
            $aresResponse = simplexml_load_string($aresRequest);

            if ($aresResponse) {
                $ns = $aresResponse->getDocNamespaces();
                $data = $aresResponse->children($ns['are']);
                $elements = $data->children($ns['D'])->VBAS;

                $ico = (int) $elements->ICO;
                if ($ico !== $id) {
                    throw new AresException('IČ firmy nebylo nalezeno.');
                }

                $record = new AresRecord(
                    $id,
                    strval($elements->DIC), // taxId
                    strval($elements->OF),  // companyName
                    strval($elements->AA->NU), // street
                    strval($elements->AA->CD), // house number
                    strval($elements->AA->CO) ?: null, // house orientation number
                    strval($elements->AA->NCO) ? strval($elements->AA->N.' - '.strval($elements->AA->NCO)) : strval($elements->AA->N), // town
                    strval($elements->AA->PSC) // ZIP
                );

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
     * @param $id
     *
     * @throws InvalidArgumentException
     * @throws Ares\AresException
     *
     * @return AresRecord
     */
    public function findInResById($id)
    {
        $id = Lib::toInteger($id);
        $this->ensureIdIsInteger($id);

        // Sestaveni URL
        $url = sprintf(self::URL_RES, $id);

        $cachedFileName = $id.'_'.date($this->cacheStrategy).'.php';
        $cachedFile = $this->cacheDir.'/res_'.$cachedFileName;
        $cachedRawFile = $this->cacheDir.'/res_raw_'.$cachedFileName;

        if (is_file($cachedFile)) {
            return unserialize(file_get_contents($cachedFile));
        }

        try {
            $aresRequest = file_get_contents($url, null, stream_context_create($this->contextOptions));
            if ($this->debug) {
                file_put_contents($cachedRawFile, $aresRequest);
            }
            $aresResponse = simplexml_load_string($aresRequest);

            if ($aresResponse) {
                $ns = $aresResponse->getDocNamespaces();
                $data = $aresResponse->children($ns['are']);
                $elements = $data->children($ns['D'])->Vypis_RES;

                if (strval($elements->ZAU->ICO) === $id) {
                    $record = new AresRecord(
                        strval($id),
                        $this->findVatById($id),
                        strval($elements->ZAU->OF),
                        strval($elements->SI->NU),
                        strval($elements->SI->CD),
                        strval($elements->SI->CO),
                        strval($elements->SI->N),
                        strval($elements->SI->PSC)
                    );
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
     * @param $id
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     *
     * @return TaxRecord|mixed
     */
    public function findVatById($id)
    {
        $id = Lib::toInteger($id);

        $this->ensureIdIsInteger($id);

        // Sestaveni URL
        $url = sprintf(self::URL_TAX, $id);

        $cachedFileName = $id.'_'.date($this->cacheStrategy).'.php';
        $cachedFile = $this->cacheDir.'/tax_'.$cachedFileName;
        $cachedRawFile = $this->cacheDir.'/tax_raw_'.$cachedFileName;

        if (is_file($cachedFile)) {
            return unserialize(file_get_contents($cachedFile));
        }

        try {
            $vatRequest = file_get_contents($url, null, stream_context_create($this->contextOptions));
            if ($this->debug) {
                file_put_contents($cachedRawFile, $vatRequest);
            }
            $vatResponse = simplexml_load_string($vatRequest);

            if ($vatResponse) {

                $ns = $vatResponse->getDocNamespaces();
                $data = $vatResponse->children($ns['are']);
                $elements = $data->children($ns['dtt'])->V->S;

                if (strval($elements->ico) === $id) {
                    $record = new TaxRecord(
                        str_replace('dic=', 'CZ', strval($elements->p_dph))
                    );
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
     * @param $name
     * @param null $city
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     *
     * @return array|AresRecord[]|AresRecords
     */
    public function findByName($name, $city = null)
    {
        if (strlen($name) < 3) {
            throw new InvalidArgumentException('Zadejte minimálně 3 znaky pro hledání.');
        }

        $url = sprintf(
            self::URL_FIND,
            urlencode(Lib::stripDiacritics($name)),
            urlencode(Lib::stripDiacritics($city))
        );

        $cachedFileName = date($this->cacheStrategy).'_'.md5($name.$city).'.php';
        $cachedFile = $this->cacheDir.'/find_'.$cachedFileName;
        $cachedRawFile = $this->cacheDir.'/find_raw_'.$cachedFileName;

        if (is_file($cachedFile)) {
            return unserialize(file_get_contents($cachedFile));
        }

        $aresRequest = file_get_contents($url, null, stream_context_create($this->contextOptions));
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

        if (!count($elements)) {
            throw new AresException('Nic nebylo nalezeno.');
        }

        $records = new AresRecords();
        foreach ($elements as $element) {
            // TODO: What is this?
            $record = new AresRecord();
            $record->setCompanyId(strval($element->ico));
            $record->setTaxId(
                ($element->dph ? str_replace('dic=', 'CZ', strval($element->p_dph)) : '')
            );
            $record->setCompanyName(strval($element->ojm));
            //'adresa' => strval($element->jmn));
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
     * @param mixed $id
     */
    private function ensureIdIsInteger($id)
    {
        if (!is_int($id)) {
            throw new InvalidArgumentException('IČ firmy musí být číslo.');
        }
    }
}
