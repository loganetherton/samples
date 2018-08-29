<?php
namespace ValuePad\DAL\Asc\Support\Import;

use Iterator;
use ValuePad\Core\Asc\Enums\Certifications;
use ValuePad\Core\Asc\Persistables\AppraiserPersistable;

class Producer implements Iterator
{
	/**
	 * @var resource
	 */
	private $resource;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var string|false
	 */
	private $buffer;

	/**
	 * @var int
	 */
	private $counter = 0;

	/**
	 * @var array
	 */
	private $columns = [];

	/**
	 * @var Row
	 */
	private $row;

	/**
	 * @param string $path
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * @return AppraiserPersistable
	 */
	public function current()
	{
		$persistable = new AppraiserPersistable();

		$persistable->setCertifications(new Certifications([$this->row->getCertification()]));
		$persistable->setLicenseState($this->row->getLicenseState());
		$persistable->setLicenseNumber($this->row->getLicenseNumber());
		$persistable->setAddress($this->row->getAddress());
		$persistable->setCompanyName($this->row->getCompanyName());
		$persistable->setCity($this->row->getCity());
		$persistable->setFirstName($this->row->getFirstName());
		$persistable->setLastName($this->row->getLastName());

		$licenseExpiresAt = $this->row->getLicenseExpiresAt();

		if ($licenseExpiresAt){
			$persistable->setLicenseExpiresAt($licenseExpiresAt);
		}

		$persistable->setPhone($this->row->getPhone());
		$persistable->setZip($this->row->getZip());
		$persistable->setState($this->row->getState());

		return $persistable;
	}

	/**
	 * @param $dest
	 * @return AppraiserPersistable[]
	 */
	public static function onlyActive($dest)
	{
		return new ProducerFilter(new static($dest));
	}

	/**
	 * @return bool
	 */
	public function isActive()
	{
		return $this->row->isActive();
	}

	public function next()
	{
		$this->read();

		if ($this->isEnd()){
			return ;
		}

		$this->extractRow();
		$this->counter ++;
	}

	public function key()
	{
		return $this->counter;
	}

	public function valid()
	{
		if ($this->isEnd()){
			$this->close();
			return false;
		}

		return true;
	}

	public function rewind()
	{
		$this->counter = 0;

		if ($this->isOpen()){
			$this->close();
		}

		$this->resource = fopen($this->path, 'r');

		$this->read();

		if ($this->isEnd()){
			return ;
		}

		$this->extractColumns();

		$this->read();

		if ($this->isEnd()){
			return ;
		}

		$this->extractRow();
	}

	/**
	 * Closes file and set null to the resource property
	 */
	private function close()
	{
		fclose($this->resource);
		$this->resource = null;
	}

	private function read()
	{
		$this->buffer = fgets($this->resource);
	}

	private function extractColumns()
	{
		$columns = preg_split("/[\t]/", $this->buffer);
		$this->columns = array_map('trim', $columns);
	}

	private function extractRow()
	{
		$row = preg_split("/[\t]/", $this->buffer);
		$row = array_map('trim', $row);

		$data = [];

		foreach ($row as $index => $value){
			$data[$this->columns[$index]] = $value;
		}

		$this->row = new Row($data);
	}

	/**
	 * @return bool
	 */
	private function isOpen()
	{
		return $this->resource !== null;
	}

	/**
	 * @return bool
	 */
	private function isEnd()
	{
		return $this->buffer === false;
	}

	public function __destruct()
	{
		if ($this->isOpen()){
			$this->close();
		}
	}
}
