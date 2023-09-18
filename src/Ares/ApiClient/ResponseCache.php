<?php declare(strict_types = 1);

namespace Defr\Ares\ApiClient;

use Defr\Ares\AresException;
use Defr\Ares\AresRecord;
use Defr\Ares\AresRecords;
use Defr\Ares\TaxRecord;
use Defr\Lib;


class ResponseCache implements ApiClient
{

	private string $cacheStrategy = 'YW';

	/**
	 * Path to directory that serves as an local cache for Ares service responses.
	 */
	private ?string $cacheDir = null;

	/**
	 * Whether we are running in debug/development environment.
	 */
	private bool $debug;

	private ApiClient $inner;

	/**
	 * Last called URL.
	 */
	private string $lastUrl;

	public function __construct(ApiClient $innerApiClient, ?string $cacheDir = null, bool $debug = false, ?string $balancer = null)
	{
		$this->inner = $innerApiClient;

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
	 * {@inheritDoc}
	 */
	public function findByIdentificationNumber($id): AresRecord
	{
		$cachedFileName = $id.'_'.date($this->cacheStrategy).'.php';
		$cachedFile = $this->cacheDir.'/bas_'.$cachedFileName;

		if (is_file($cachedFile)) {
			return unserialize(file_get_contents($cachedFile));
		}

		$record = $this->inner->findByIdentificationNumber($id);

		file_put_contents($cachedFile, serialize($record));

		return $record;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findInResById(int $id): AresRecord
	{
		$cachedFileName = $id.'_'.date($this->cacheStrategy).'.php';
		$cachedFile = $this->cacheDir.'/res_'.$cachedFileName;

		if (is_file($cachedFile)) {
			return unserialize(file_get_contents($cachedFile));
		}

		$record = $this->inner->findInResById($id);

		file_put_contents($cachedFile, serialize($record));

		return $record;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findVatById(int $id): AresRecord
	{
		$cachedFileName = $id.'_'.date($this->cacheStrategy).'.php';
		$cachedFile = $this->cacheDir.'/tax_'.$cachedFileName;

		if (is_file($cachedFile)) {
			return unserialize(file_get_contents($cachedFile));
		}

		$record = $this->inner->findVatById($id);

		file_put_contents($cachedFile, serialize($record));

		return $record;
	}

	/**
	 * {@inheritDoc}
	 */
	public function findByName(string $name, ?string $city = null)
	{
		$cachedFileName = date($this->cacheStrategy).'_'.md5($name.$city).'.php';
		$cachedFile = $this->cacheDir.'/find_'.$cachedFileName;

		if (is_file($cachedFile)) {
			return unserialize(file_get_contents($cachedFile));
		}

		$records = $this->inner->findByName($name, $city);

		file_put_contents($cachedFile, serialize($records));

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
		$this->inner->setBalancer($balancer);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastUrl(): string
	{
		return $this->inner->getLastUrl();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLastRawResponse(): ?string
	{
		return $this->inner->getLastRawResponse();
	}

}
