<?php
namespace ValuePad\Api\Assignee\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Api\Support\Validation\Rules\MonthYearPair;
use ValuePad\Core\Payment\Objects\CreditCardRequisites;

class CreditCardProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'number' => 'string',
			'code' => 'string',
			'expiresAt' => new MonthYearPair(),
            'address' => 'string',
            'city' => 'string',
            'state' => 'string',
            'zip' => 'string'
		];
	}

	/**
	 * @return CreditCardRequisites
	 */
	public function createRequisites()
	{
		return $this->populate(new CreditCardRequisites());
	}
}
