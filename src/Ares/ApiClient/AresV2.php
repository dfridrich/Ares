<?php declare(strict_types = 1);

namespace Defr\Ares\ApiClient;

use Defr\Ares\AresException;
use Defr\Ares\AresRecord;
use Defr\Ares\AresRecords;
use Defr\Ares\TaxRecord;
use Defr\Lib;


class AresV2 implements ApiClient
{

	private const URL_BAS = 'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/%s';
	private const URL_RES = 'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/res/%s';
	// todo url?
	private const URL_TAX = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?ico=%s&filtr=0';
	// todo url?
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
			$aresResponse = json_decode($aresRequest);

			if ($aresResponse) {
				$ico = $aresResponse->ico;
				if ((string) $ico !== (string) $id) {
					throw new AresException('IČ firmy nebylo nalezeno.');
				}

				$record = new AresRecord();

				$record->setCompanyId(strval($aresResponse->ico));
				$record->setTaxId('CZ'.$aresResponse->dic);
				$record->setCompanyName(strval($aresResponse->obchodniJmeno));
				$record->setStreet(strval($aresResponse->sidlo->nazevUlice));
				$record->setStreetHouseNumber(strval($aresResponse->sidlo->cisloDomovni));
				$record->setStreetOrientationNumber($aresResponse->sidlo->cisloOrientacni . ($aresResponse->sidlo->cisloOrientacniPismeno ?? ''));

				if (strval($aresResponse->sidlo->nazevObce) === 'Praha') {
					$record->setTown(strval($aresResponse->sidlo->nazevMestskeCastiObvodu));
					$record->setArea(strval($aresResponse->sidlo->nazevCastiObce));
				} elseif (strval($aresResponse->sidlo->nazevCastiObce) !== strval($aresResponse->sidlo->nazevObce)) {
					$record->setTown(strval($aresResponse->sidlo->nazevObce));
					$record->setArea(strval($aresResponse->sidlo->nazevCastiObce));
				} else {
					$record->setTown($aresResponse->sidlo->nazevObce);
				}

				$record->setZip(strval($aresResponse->sidlo->psc));

				// todo A/N are present, but don't know where E/Z could be taken from
				$record->setRegisters(strval($aresResponse->seznamRegistraci->stavZdrojeVr ?? null));

				// todo this does not match in all cases
				$record->setStateCode(strval($aresResponse->sidlo->kodKraje ?? null));
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
			$aresResponse = json_decode($aresRequest);

			// todo rewrite

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
			$vatResponse = json_decode($vatRequest);

			// todo rewrite

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
		$aresResponse = json_decode($aresRequest);
		if (!$aresResponse) {
			throw new AresException('Databáze ARES není dostupná.');
		}

		// todo rewrite

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
