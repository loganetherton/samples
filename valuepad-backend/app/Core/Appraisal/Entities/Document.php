<?php
namespace ValuePad\Core\Appraisal\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use ValuePad\Core\Document\Support\DocumentUsageManagementTrait;
use ValuePad\Core\Document\Entities\Document as Source;
use ValuePad\Core\Document\Enums\Format as SourceFormat;
use DateTime;

class Document
{
	use DocumentUsageManagementTrait;

	/**
	 * @var int
	 */
	private $id;
	public function setId($id) { $this->id = $id; }
	public function getId() { return $this->id; }

	/**
	 * @var DateTime
	 */
	private $createdAt;
	public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
	public function getCreatedAt() { return $this->createdAt; }

	/**
	 * @var Source[]
	 */
	private $primaries;
	public function getPrimaries() { return $this->primaries; }

	/**
	 * @return Source
	 */
	public function getPrimary()
	{
		$total = $this->primaries->count();

		if ($total === 0){
			return null;
		}

		if ($total === 1){
			return $this->primaries->first();
		}

		if ($pdf = array_first($this->primaries, function($key, Source $document){
			return $document->getFormat()->is(SourceFormat::PDF);
		})){
			return $pdf;
		}

		return $this->primaries->first();
	}

	/**
	 * @param Source $source
	 */
	public function addPrimary(Source $source)
	{
		$sources = [];

		foreach ($this->primaries as $primary){
			if ($primary->getId() == $source->getId()){
				return ;
			}

			$sources[] = $primary;
		}

		$sources[] = $source;

		$this->handleUsageOfMultipleDocuments($this->primaries, $sources);

		$this->primaries->add($source);
	}

	public function clearPrimaries()
	{
		$this->handleUsageOfMultipleDocuments($this->primaries, []);

		$this->primaries->clear();
	}

	/**
	 * @var Source[]
	 */
	private $extra;
	public function getExtra() { return $this->extra; }

	/**
	 * @param Source[] $sources
	 */
	public function setExtra($sources)
	{
		$this->handleUsageOfMultipleDocuments($this->getExtra(), $sources);

		$this->extra->clear();

		foreach ($sources as $source){
			$this->extra->add($source);
		}
	}

	public function clearExtra()
	{
		$this->handleUsageOfMultipleDocuments($this->getExtra(), []);
		$this->extra->clear();
	}


	/**
	 * @var Order
	 */
	private $order;
	public function setOrder(Order $order) { $this->order = $order; }
	public function getOrder() { return $this->order; }

	/**
	 * @var bool
	 */
	private $showToAppraiser;
	public function setShowToAppraiser($flag) { $this->showToAppraiser = $flag; }

	/**
	 * @return bool
	 */
	public function getShowToAppraiser()
	{
		if ($this->showToAppraiser === null){
			return $this->getOrder()->getCustomer()->getSettings()->getShowDocumentsToAppraiser();
		}

		return $this->showToAppraiser;
	}

	public function __construct()
	{
		$this->extra = new ArrayCollection();
		$this->primaries = new ArrayCollection();
	}
}
