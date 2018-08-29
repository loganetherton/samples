<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Customer\Persistables\AdditionalStatusPersistable;

class AdditionalStatusesProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'title' => 'string',
			'comment' => 'string'
		];
	}

	/**
	 * @return AdditionalStatusPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new AdditionalStatusPersistable());
	}
}
