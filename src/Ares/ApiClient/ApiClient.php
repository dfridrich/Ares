<?php declare(strict_types = 1);

namespace Defr\Ares\ApiClient;

use Defr\Ares\AresException;
use Defr\Ares\AresRecord;
use Defr\Ares\AresRecords;


interface ApiClient
{

	/**
	 * @param mixed $id
	 * @throws AresException
	 */
	public function findByIdentificationNumber($id): AresRecord;

	/**
	 * Find subject in RES (Registr ekonomických subjektů)
	 *
	 * @throws AresException
	 */
	public function findInResById(int $id): AresRecord;

	/**
	 * Find subject's tax ID in OR (Obchodní rejstřík).
	 *
	 * @throws \Exception
	 */
	public function findVatById(int $id): AresRecord;

	/**
	 * Find subject by its name in OR (Obchodní rejstřík).
	 *
	 * @throws AresException
	 *
	 * @return array|AresRecord[]|AresRecords
	 */
	public function findByName(string $name, ?string $city = null);

	public function setCacheStrategy(string $cacheStrategy): void;

	public function setDebug(bool $debug): void;

	/**
	 * @return static
	 */
	public function setBalancer(string $balancer);

	public function getLastUrl(): string;

}
