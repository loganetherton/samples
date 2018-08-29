<?php
namespace ValuePad\Core\Appraisal\Entities;

use ValuePad\Core\Appraisal\Objects\Conditions;

class AcceptedConditions extends Conditions
{
	/**
	 * @var int
	 */
	private $id;
	public function setId($id) { $this->id = $id; }
	public function getId() { return $this->id; }

	/**
	 * @var string
	 */
	private $additionalComments;
	public function setAdditionalComments($comments) { $this->additionalComments = $comments; }
	public function getAdditionalComments() { return $this->additionalComments; }
}
