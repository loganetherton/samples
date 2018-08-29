<?php
namespace ValuePad\Api\Shared\Processors;

use ValuePad\Api\Shared\Support\AvailabilityConfigurationTrait;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Shared\Persistables\AvailabilityPersistable;

class AvailabilityProcessor extends BaseProcessor
{
	use AvailabilityConfigurationTrait;

	/**
	 * @return array
	 */
	protected function configuration()
	{
		return $this->getAvailabilityConfiguration();
	}

	/**
	 * @return AvailabilityPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new AvailabilityPersistable());
	}
}
