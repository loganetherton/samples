<?php
namespace ValuePad\Core\Shared\Options;

use ValuePad\Core\Support\Criteria\Criteria;

trait CriteriaAwareTrait
{
	/**
	 * @var Criteria[]
	 */
	private $criteria = [];

	/**
	 * @param Criteria[] $criteria
	 */
	public function setCriteria(array $criteria)
	{
		$this->criteria = $criteria;
	}

	/**
	 * @return Criteria[]
	 */
	public function getCriteria()
	{
		return $this->criteria;
	}
}
