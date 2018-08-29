<?php
namespace ValuePad\Core\Appraisal\Options;

trait RequireEstimatedCompletionDateOptionTrait
{
	/**
	 * @var bool
	 */
	private $isEstimatedCompletionDateRequired = true;

	/**
	 * @param bool $flag
	 * @return $this
	 */
	public function requireEstimatedCompletionDate($flag)
	{
		$this->isEstimatedCompletionDateRequired = $flag;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isEstimatedCompletionDateRequired()
	{
		return $this->isEstimatedCompletionDateRequired;
	}
}
