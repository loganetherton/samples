<?php
namespace ValuePad\Api\Shared\Support;
use ValuePad\Api\Appraiser\V2_0\Support\ConfigurationUtilities;

trait AvailabilityConfigurationTrait
{
	/**
	 * @param array $options
	 * @return array
	 */
	protected function getAvailabilityConfiguration(array $options = [])
	{
		$namespace = ConfigurationUtilities::resolveNamespaceFromOptions($options);

		return [
			$namespace . 'isOnVacation' => 'bool',
			$namespace . 'from' => 'datetime',
			$namespace . 'to' => 'datetime',
			$namespace . 'message' => 'string'
		];
	}
}
