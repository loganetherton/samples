<?php
namespace ValuePad\Core\Appraisal\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Support\Service\ContainerInterface;

class AdditionalDocumentValidator extends AbstractThrowableValidator
{
	use AdditionalDocumentValidatorTrait;

	/**
	 * @var Customer
	 */
	private $customer;

	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @param ContainerInterface $container
	 * @param Customer $customer
	 */
	public function __construct(ContainerInterface $container, Customer $customer)
	{
		$this->container = $container;
		$this->customer = $customer;
	}

	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$this->defineAdditionalDocument($binder, $this->container, $this->customer);
	}
}
