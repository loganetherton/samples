<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Customer\Persistables\JobTypePersistable;

class JobTypesProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'title' => 'string',
			'local' => 'int',
			'isCommercial' => 'bool',
			'isPayable' => 'bool'
		];
	}

	/**
	 * @return JobTypePersistable $persistable
	 */
	public function createPersistable()
	{
		return $this->populate(new JobTypePersistable());
	}
}
