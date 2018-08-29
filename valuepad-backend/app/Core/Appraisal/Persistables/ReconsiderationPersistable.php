<?php
namespace ValuePad\Core\Appraisal\Persistables;

use ValuePad\Core\Appraisal\Objects\Comparable;

class ReconsiderationPersistable
{
	/**
	 * @var Comparable[]
	 */
	private $comparables;
	public function setComparables(array $comparables) { $this->comparables = $comparables; }
	public function getComparables() { return $this->comparables; }

	/**
	 * @var string
	 */
	private $comment;
	public function setComment($comment) { $this->comment = $comment; }
	public function getComment() { return $this->comment; }

    /**
     * @var AdditionalDocumentPersistable
     */
	private $document;
    public function setDocument(AdditionalDocumentPersistable $document) { $this->document = $document; }
    public function getDocument() { return $this->document; }

    /**
     * @var AdditionalDocumentPersistable[]
     */
    private $documents;
    public function setDocuments(array $documents) { $this->documents = $documents; }
    public function getDocuments() { return $this->documents; }
}
