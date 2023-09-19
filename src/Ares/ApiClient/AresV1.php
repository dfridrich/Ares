<?php declare(strict_types = 1);

namespace Defr\Ares\ApiClient;

use Defr\Ares\AresException;
use Defr\Ares\AresRecord;
use Defr\Ares\AresRecords;
use Defr\Ares\TaxRecord;
use Defr\Lib;


class AresV1 implements ApiClient
{

	private const URL_BAS = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=%s';
	private const URL_RES = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_res.cgi?ICO=%s';
	private const URL_TAX = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?ico=%s&filtr=0';
	private const URL_FIND = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?obch_jm=%s&obec=%s&filtr=0';

	/**
	 * Load balancing domain URL.
	 */
	private ?string $balancer = null;

	/**
	 * Stream context options.
	 *
	 * @var array{ssl: array{verify_peer: bool, verify_peer_name: bool}}
	 */
	private array $contextOptions = [
		'ssl' => [
			'verify_peer' => false,
			'verify_peer_name' => false,
		],
	];

	/**
	 * Last called URL.
	 */
	private string $lastUrl;

	private ?string $lastRawResponse = null;

	public function __construct(?string $balancer = null)
	{
		if (null !== $balancer) {
			$this->balancer = $balancer;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function findByIdentificationNumber($id): AresRecord
	{
		$url = $this->composeUrl(sprintf(self::URL_BAS, $id));

		try {
			$aresRequest = file_get_contents($url, false, stream_context_create($this->contextOptions));
			$this->lastRawResponse = $aresRequest;
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

		return $record;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findInResById(int $id): AresRecord
	{
		$url = $this->composeUrl(sprintf(self::URL_RES, $id));

		try {
			$aresRequest = file_get_contents($url, false, stream_context_create($this->contextOptions));
			$this->lastRawResponse = $aresRequest;
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

		return $record;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findVatById(int $id): AresRecord
	{
		$url = $this->composeUrl(sprintf(self::URL_TAX, $id));

		try {
			$vatRequest = file_get_contents($url, false, stream_context_create($this->contextOptions));
			$this->lastRawResponse = $vatRequest;
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

		return $record;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findByName(string $name, ?string $city = null)
	{
		$url = $this->composeUrl(sprintf(
			self::URL_FIND,
			urlencode(Lib::stripDiacritics($name)),
			urlencode(Lib::stripDiacritics($city))
		));

		$aresRequest = file_get_contents($url, false, stream_context_create($this->contextOptions));
		$this->lastRawResponse = $aresRequest;
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

		return $records;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setCacheStrategy(string $cacheStrategy): void
	{
		$this->cacheStrategy = $cacheStrategy;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDebug(bool $debug): void
	{
		$this->debug = $debug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setBalancer(string $balancer)
	{
		$this->balancer = $balancer;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastUrl(): string
	{
		return $this->lastUrl;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastRawResponse(): ?string
	{
		return $this->lastRawResponse;
	}

	private function composeUrl(string $url): string
	{
		if ($this->balancer) {
			$url = sprintf('%s?url=%s', $this->balancer, urlencode($url));
		}

		$this->lastUrl = $url;

		return $url;
	}

}
