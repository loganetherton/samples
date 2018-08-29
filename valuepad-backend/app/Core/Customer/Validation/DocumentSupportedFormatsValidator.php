<?php
namespace ValuePad\Core\Customer\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Customer\Services\DocumentSupportedFormatsService;
use ValuePad\Core\Customer\Validation\Rules\JobTypeBelongs;
use ValuePad\Core\Customer\Validation\Rules\JobTypeIsAvailableForDocumentSupportedFormats;

class DocumentSupportedFormatsValidator extends AbstractThrowableValidator
{
	/**
	 * @var CustomerService
	 */
	private $customerService;

	/**
	 * @var DocumentSupportedFormatsService
	 */
	private $formatsService;

	/**
	 * @var Customer
	 */
	private $customer;

	/**
	 * @var JobType
	 */
	private $ignoredJobType;

	/**
	 * @param CustomerService $customerService
	 * @param DocumentSupportedFormatsService $formatsService
	 * @param Customer $customer
	 */
	public function __construct(
		CustomerService $customerService,
		DocumentSupportedFormatsService $formatsService,
		Customer $customer
	)
	{
		$this->customerService = $customerService;
		$this->formatsService = $formatsService;
		$this->customer = $customer;
	}

	/**
	 * @param JobType $jobType
	 * @return $this
	 */
	public function ignoreJobType(JobType $jobType)
	{
		$this->ignoredJobType = $jobType;
		return $this;
	}

	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$binder->bind('jobType', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new JobTypeBelongs($this->customerService, $this->customer))
				->addRule(new JobTypeIsAvailableForDocumentSupportedFormats(
					$this->formatsService,
					$this->customer,
					$this->ignoredJobType
				));
		});

		$binder->bind('primary', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank());
		});
	}
}
