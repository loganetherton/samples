<?php
namespace ValuePad\Core\Appraisal\Persistables;

use ValuePad\Core\Document\Persistables\Identifier;

class AdditionalDocumentPersistable
{
	/**
	 * @var string
	 */
	private $label;
	public function setLabel($label) { $this->label = $label; }
	public function getLabel() { return $this->label; }

	/**
	 * @var int
	 */
	private $type;
	public function setType($type) { $this->type = $type; }
	public function getType() { return $this->type; }

	/**
	 * @var Identifier
	 */
	private $document;
	public function setDocument(Identifier $identifier) { $this->document = $identifier; }
	public function getDocument() { return $this->document; }
}
