<?php
namespace ValuePad\Api\Assignee\V2_0\Processors;

use ValuePad\Api\Appraisal\V2_0\Support\ConditionsConfigurationTrait;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Appraisal\Objects\Conditions;

class ConditionsProcessor extends BaseProcessor
{
	use ConditionsConfigurationTrait;

	protected function configuration()
	{
		return $this->getConditionsConfiguration();
	}

	/**
	 * @return Conditions
	 */
	public function createConditions()
	{
		return $this->populate(new Conditions());
	}
}
