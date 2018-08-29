<?php
namespace ValuePad\Api\Customer\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Customer\Entities\Settings;

class SettingsTransformer extends BaseTransformer
{
	/**
	 * @param Settings $settings
	 * @return array
	 */
	public function transform($settings)
	{
		return $this->extract($settings);
	}
}
