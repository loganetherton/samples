<?php
namespace ValuePad\Core\Customer\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Customer\Entities\AdditionalStatus;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Customer\Validation\Rules\AdditionalStatusUnique;

class AdditionalStatusValidator extends AbstractThrowableValidator
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
	 * @var AdditionalStatus
	 */
	private $currentAdditionalStatus;

	/**
	 * @param CustomerService $customerService
	 * @param Customer $customer
	 * @param AdditionalStatus $currentAdditionalStatus
	 */
	public function __construct(
		CustomerService $customerService,
		Customer $customer,
		AdditionalStatus $currentAdditionalStatus = null
	)
	{
		$this->customerService = $customerService;
		$this->customer = $customer;
		$this->currentAdditionalStatus = $currentAdditionalStatus;
	}

	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$binder->bind('title', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank())
				->addRule(new Length(1, 255))
				->addRule(new AdditionalStatusUnique($this->customerService, $this->customer, $this->currentAdditionalStatus));
		});
	}
}
