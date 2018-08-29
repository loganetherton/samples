<?php
namespace ValuePad\Api\Appraiser\V2_0\Processors;

use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Persistables\AchPersistable;

class AchProcessor extends BaseProcessor
{
	/**
	 * @return array
	 */
	protected function configuration()
	{
		return [
			'bankName' => 'string',
			'accountType' => new Enum(AchAccountType::class),
			'accountNumber' => 'string',
			'routing' => 'string'
		];
	}

	/**
	 * @return AchPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new AchPersistable());
	}
}
