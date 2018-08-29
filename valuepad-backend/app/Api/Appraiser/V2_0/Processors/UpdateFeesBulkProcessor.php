<?php
namespace ValuePad\Api\Appraiser\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;

class UpdateFeesBulkProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'bulk' => [
				'id' => 'int',
				'amount' => 'float'
			]
		];
	}

	/**
	 * @return array
	 */
	public function getAmounts()
	{
		$amounts = [];

		foreach ($this->get('bulk', []) as $item){
			$amounts[$item['id']] = $item['amount'];
		}

		return $amounts;
	}
}
