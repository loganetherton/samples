<?php
namespace ValuePad\Core\Appraisal\Entities;

use ValuePad\Core\Appraisal\Objects\Comparable;

use DateTime;

class Reconsideration
{
	/**
	 * @var int
	 */
	private $id;
	public function setId($id) { $this->id = $id; }
	public function getId() { return $this->id; }

	/**
	 * @var Comparable[]
	 */
	private $comparables = [];
	public function setComparables(array $comparables) { $this->comparables = $comparables; }
	public function getComparables() { return $this->comparables; }

	/**
	 * @var DateTime
	 */
	private $createdAt;
	public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
	public function getCreatedAt() { return $this->createdAt; }

	/**
	 * @var string
	 */
	private $comment;
	public function setComment($comment) { $this->comment = $comment; }
	public function getComment() { return $this->comment; }

	/**
	 * @var Order
	 */
	private $order;
	public function setOrder(Order $order) { $this->order = $order; }
	public function getOrder() { return $this->order; }

    /**
     * @var AdditionalDocument
     */
	private $document;
    public function setDocument(AdditionalDocument $document) { $this->document = $document; }
    public function getDocument() { return $this->document; }

    /**
     * @var AdditionalDocument[]
     */
    private $documents = [];
    public function setDocuments(array $documents) { $this->documents = $documents; }
    public function getDocuments() { return $this->documents; }
}
