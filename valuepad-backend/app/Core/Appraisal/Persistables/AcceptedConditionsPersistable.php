<?php
namespace ValuePad\Core\Appraisal\Persistables;

use ValuePad\Core\Appraisal\Objects\Conditions;

class AcceptedConditionsPersistable extends Conditions
{
	/**
	 * @var string
	 */
	private $additionalComments;
	public function setAdditionalComments($comments) { $this->additionalComments = $comments; }
	public function getAdditionalComments() { return $this->additionalComments; }}
