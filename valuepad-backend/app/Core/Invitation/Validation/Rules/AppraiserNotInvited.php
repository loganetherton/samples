<?php
namespace ValuePad\Core\Invitation\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Customer\Entities\Customer;

class AppraiserNotInvited extends AbstractRule
{
	/**
	 * @var Customer
	 */
	private $customer;

	/**
	 * @var AppraiserService
	 */
	private $appraiserService;

	/**
	 * @param AppraiserService $appraiserService
	 * @param Customer $customer
	 */
	public function __construct(AppraiserService $appraiserService, Customer $customer)
	{
		$this->appraiserService = $appraiserService;
		$this->customer = $customer;

		$this->setIdentifier('already-invited');
		$this->setMessage('The invitation has been sent already to the provided appraiser.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if ($this->appraiserService->hasPendingInvitationFromCustomer($value, $this->customer->getId())){
			return $this->getError();
		}

		return null;
	}
}
