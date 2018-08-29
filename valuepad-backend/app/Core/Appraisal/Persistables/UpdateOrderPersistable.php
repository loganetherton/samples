<?php
namespace ValuePad\Core\Appraisal\Persistables;

class UpdateOrderPersistable extends AbstractOrderPersistable
{
	/**
	 * @var int
	 */
	private $contractDocument;
	public function setContractDocument($document) { $this->contractDocument = $document; }
	public function getContractDocument() { return $this->contractDocument; }
}
