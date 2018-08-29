<?php
namespace ValuePad\Core\Appraiser\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;

class JobTypeAccess extends AbstractRule
{
	/**
	 * @var CustomerService
	 */
	private $customerService;

	/**
	 * @var AppraiserService
	 */
	private $appraiserService;

	/**
	 * @var Customer
	 */
	private $customer;

	/**
	 * @var Appraiser
	 */
	private $appraiser;

	/**
	 * @param CustomerService $customerService
	 * @param AppraiserService $appraiserService
	 * @param Customer $customer
	 * @param Appraiser $appraiser
	 */
	public function __construct(
		CustomerService $customerService,
		AppraiserService $appraiserService,
		Customer $customer,
		Appraiser $appraiser
	)
	{
		$this->customerService = $customerService;
		$this->appraiserService = $appraiserService;
		$this->customer = $customer;
		$this->appraiser = $appraiser;

		$this->setIdentifier('access');
		$this->setMessage('Unable to proceed with the provided job type.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if (!$this->customerService->hasPayableJobType($this->customer->getId(), $value)){
			return $this->getError();
		}

		if (!$this->appraiserService->hasPendingInvitationFromCustomer($this->appraiser->getId(), $this->customer->getId())
			&& !$this->appraiserService->isRelatedWithCustomer($this->appraiser->getId(), $this->customer->getId())){
			return $this->getError();
		}

		return null;
	}
}
