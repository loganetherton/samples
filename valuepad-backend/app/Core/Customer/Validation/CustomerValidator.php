<?php
namespace ValuePad\Core\Customer\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Shared\Validation\Rules\Phone;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\User\Services\UserService;
use ValuePad\Core\User\Validation\Inflators\PasswordInflator;
use ValuePad\Core\User\Validation\Inflators\UsernameInflator;

class CustomerValidator extends AbstractThrowableValidator
{
	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var EnvironmentInterface
	 */
	private $environment;

	/**
	 * @var Customer
	 */
	private $currentCustomer;

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->userService = $container->get(UserService::class);
		$this->environment = $container->get(EnvironmentInterface::class);
	}

	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$binder->bind('name', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank());
		});

		$binder->bind('phone', function(Property $property){
			$property
				->addRule(new Phone());
		});

		$binder->bind('username', new UsernameInflator($this->userService, $this->environment, $this->currentCustomer));
		$binder->bind('password', new PasswordInflator($this->environment));
	}

	/**
	 * @param Customer $customer
	 * @return $this
	 */
	public function setCurrentCustomer(Customer $customer)
	{
		$this->currentCustomer = $customer;
		return $this;
	}
}
