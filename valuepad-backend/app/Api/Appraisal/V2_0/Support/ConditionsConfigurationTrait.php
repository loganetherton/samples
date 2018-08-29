<?php
namespace ValuePad\Api\Appraisal\V2_0\Support;

use ValuePad\Api\Appraiser\V2_0\Support\ConfigurationUtilities;
use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Core\Appraisal\Enums\Request;

trait ConditionsConfigurationTrait
{
	/**
	 * @param array $options
	 * @return array
	 */
	protected function getConditionsConfiguration(array $options = [])
	{
		$namespace = ConfigurationUtilities::resolveNamespaceFromOptions($options);

		return [
			$namespace . 'request' => new Enum(Request::class),
			$namespace . 'fee' => 'float',
			$namespace . 'dueDate' => 'datetime',
			$namespace . 'explanation' => 'string'
		];
	}
}
