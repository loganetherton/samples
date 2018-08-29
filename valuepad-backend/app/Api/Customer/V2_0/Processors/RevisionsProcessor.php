<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Appraisal\Persistables\RevisionPersistable;

class RevisionsProcessor extends BaseProcessor
{
	/**
	 * @return array
	 */
	protected function configuration()
	{
		return [
			'checklist' => 'string[]',
			'message' => 'string'
		];
	}

	/**
	 * @return RevisionPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new RevisionPersistable());
	}
}
