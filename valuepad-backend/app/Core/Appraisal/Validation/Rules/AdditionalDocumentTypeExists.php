<?php
namespace ValuePad\Core\Appraisal\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;

class AdditionalDocumentTypeExists extends AbstractRule
{
	/**
	 * @var Customer
	 */
	private $customer;

	/**
	 * @var CustomerService
	 */
	private $customerService;

	/**
	 * @param CustomerService $customerService
	 * @param Customer $customer
	 */
	public function __construct(CustomerService $customerService, Customer $customer)
	{
		$this->customer = $customer;
		$this->customerService = $customerService;

		$this->setIdentifier('exists');
		$this->setMessage('The provided additional document type does not belong to the provided customer.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if (!$this->customerService->hasAdditionalDocumentType($this->customer->getId(), $value)){
			return $this->getError();
		}

		return null;
	}
}
