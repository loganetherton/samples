<?php
namespace ValuePad\Core\Customer\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Customer\Services\CustomerService;

class JobTypeBelongs extends AbstractRule
{
	/**
	 * @var CustomerService
	 */
	private $customerService;

	/**
	 * @var Customer
	 */
	private $customer;

	/**
	 * @var JobType
	 */
	private $trustedJobType;

	/**
	 * @var bool
	 */
	private $isRelaxed;

	/**
	 * @param CustomerService $customerService
	 * @param Customer $customer
	 * @param bool $isRelaxed
	 */
	public function __construct(CustomerService $customerService, Customer $customer, $isRelaxed = false)
	{
		$this->isRelaxed = $isRelaxed;
		$this->customerService = $customerService;
		$this->customer = $customer;

		$this->setIdentifier('not-belong');
		$this->setMessage('The provided job type does not belong to the specified customer.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if ($this->trustedJobType && $value == $this->trustedJobType->getId()){
			return null;
		}

		if (!$this->hasJobType($value)){
			return $this->getError();
		}

		return null;
	}

	/**
	 * @param int $value
	 * @return bool
	 */
	private function hasJobType($value)
	{
		if ($this->isRelaxed){
			return $this->customerService->hasJobType($this->customer->getId(), $value);
		}

		return $this->customerService->hasVisibleJobType($this->customer->getId(), $value);
	}

	/**
	 * @param JobType $jobType
	 * @return $this
	 */
	public function setTrustedJobType(JobType $jobType)
	{
		$this->trustedJobType = $jobType;
		return $this;
	}
}
