<?php
namespace ValuePad\Core\Payment\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use DateTime;
use ValuePad\Core\Shared\Objects\MonthYearPair;

class CreditCardNotExpired extends AbstractRule
{
	public function __construct()
	{
		$this->setIdentifier('expired');
		$this->setMessage('The credit card is expired.');
	}

	/**
	 * @param MonthYearPair $value
	 * @return Error|null
	 */
	public function check($value)
	{
		$providedDate = new DateTime($value->getYear().'-'.$value->getMonth().'-01 00:00:00');
		$currentDate = new DateTime((new DateTime())->format('Y').'-'.(new DateTime())->format('m').'-01 00:00:00');

		if ($providedDate < $currentDate){
			return $this->getError();
		}

		return null;
	}
}
