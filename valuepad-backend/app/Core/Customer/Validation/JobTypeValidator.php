<?php
namespace ValuePad\Core\Customer\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\JobTypeService;
use ValuePad\Core\JobType\Validation\Rules\JobTypeExists as LocalExists;
use ValuePad\Core\JobType\Services\JobTypeService as LocalService;
use ValuePad\Core\JobType\Entities\JobType as Local;
use ValuePad\Core\Support\Service\ContainerInterface;

class JobTypeValidator extends AbstractThrowableValidator
{
	/**
	 * @var LocalService
	 */
	private $localService;

	/**
	 * @var JobTypeService
	 */
	private $jobTypeService;

	/**
	 * @var Customer
	 */
	private $customer;

	/**
	 * @var Local
	 */
	private $ignored;

	/**
	 * @param Customer $customer
	 * @param ContainerInterface $container
	 * @param Local $ignored
	 */
	public function __construct(
		Customer $customer,
		ContainerInterface $container,
		Local $ignored = null
	)
	{
		$this->customer = $customer;
		$this->ignored = $ignored;

		$this->jobTypeService = $container->get(JobTypeService::class);
		$this->localService = $container->get(LocalService::class);
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
				->addRule(new Length(1, 255));
		});

		$binder->bind('local', function(Property $property){
			$property->addRule(new LocalExists($this->localService));
		});
	}
}
