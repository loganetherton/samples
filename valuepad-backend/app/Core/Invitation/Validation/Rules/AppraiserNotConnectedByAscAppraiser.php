<?php
namespace ValuePad\Core\Invitation\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Asc\Services\AscService;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;

class AppraiserNotConnectedByAscAppraiser extends AbstractRule
{
	/**
	 * @var CustomerService
	 */
	private $customerService;

	/**
	 * @var AscService
	 */
	private $ascService;

	/**
	 * @var Customer
	 */
	private $customer;

	/**
	 * @param CustomerService $customerService
	 * @param AscService $ascService
	 * @param Customer $customer
	 */
	public function __construct(CustomerService $customerService, AscService $ascService, Customer $customer)
	{
		$this->customerService = $customerService;
		$this->ascService = $ascService;
		$this->customer = $customer;

		$this->setIdentifier('already-connected');
		$this->setMessage('The customer is already connected to the provided appraiser.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		$appraiser = $this->ascService->get($value)->getAppraiser();

		if ($appraiser === null){
			return null;
		}

		if ($this->customerService->isRelatedWithAppraiser($this->customer->getId(), $appraiser->getId())){
			return $this->getError();
		}

		return null;
	}
}
