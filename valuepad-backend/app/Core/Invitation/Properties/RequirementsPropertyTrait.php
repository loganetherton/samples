<?php
namespace ValuePad\Core\Invitation\Properties;

use ValuePad\Core\Invitation\Enums\Requirements;

trait RequirementsPropertyTrait
{
	/**
	 * @var Requirements
	 */
	private $requirements;

	/**
	 * @return Requirements
	 */
	public function getRequirements()
	{
		return $this->requirements;
	}

	/**
	 * @param Requirements $requirements
	 */
	public function setRequirements(Requirements $requirements)
	{
		$this->requirements = $requirements;
	}
}
