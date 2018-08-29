<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Appraisal\Persistables\BidPersistable;

class BidsProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'amount' => 'float',
			'estimatedCompletionDate' => 'datetime',
			'comments' => 'string',
			'appraisers' => 'int[]'
		];
	}

	/**
	 * @return BidPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new BidPersistable());
	}
}
