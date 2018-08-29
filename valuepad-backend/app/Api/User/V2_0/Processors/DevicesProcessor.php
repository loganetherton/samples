<?php
namespace ValuePad\Api\User\V2_0\Processors;

use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\User\Enums\Platform;
use ValuePad\Core\User\Persistables\DevicePersistable;

class DevicesProcessor extends BaseProcessor
{
	/**
	 * @return array
	 */
	protected function configuration()
	{
		return [
			'token' => 'string',
			'platform' => new Enum(Platform::class)
		];
	}

	/**
	 * @return DevicePersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new DevicePersistable());
	}
}
