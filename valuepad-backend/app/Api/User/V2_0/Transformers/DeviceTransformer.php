<?php
namespace ValuePad\Api\User\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\User\Entities\Device;

class DeviceTransformer extends BaseTransformer
{
	/**
	 * @param Device $device
	 * @return array
	 */
	public function transform($device)
	{
		return $this->extract($device);
	}
}
