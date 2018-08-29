<?php
namespace ValuePad\Core\Invitation\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Appraiser\Validation\Rules\AppraiserExists;
use ValuePad\Core\Asc\Services\AscService;
use ValuePad\Core\Asc\Validation\Rules\AscAppraiserExists;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Invitation\Validation\Rules\AppraiserNotConnected;
use ValuePad\Core\Invitation\Validation\Rules\AppraiserNotConnectedByAscAppraiser;
use ValuePad\Core\Invitation\Validation\Rules\AppraiserNotInvited;
use ValuePad\Core\Invitation\Validation\Rules\AppraiserNotInvitedByAscAppraiser;
use ValuePad\Core\Support\Service\ContainerInterface;

class InvitationValidator extends AbstractThrowableValidator
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
	 * @var AppraiserService
	 */
	private $appraiserService;

	/**
	 * @var Customer
	 */
	private $customer;

	/**
	 * @param ContainerInterface $container
	 * @param Customer $customer
	 */
	public function __construct(ContainerInterface $container, Customer $customer)
	{
		$this->customerService = $container->get(CustomerService::class);
		$this->ascService = $container->get(AscService::class);
		$this->appraiserService = $container->get(AppraiserService::class);
		$this->customer = $customer;
	}

	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$binder->bind('appraiser', function(Property $property){
			$property->addRule(new Obligate());
		})
			->when(function(SourceHandlerInterface $source){
				return $source->getValue('ascAppraiser') === null;
			});

		$binder->bind('ascAppraiser', function(Property $property){
			$property
				->addRule(new AscAppraiserExists($this->ascService))
				->addRule(new AppraiserNotInvitedByAscAppraiser($this->customerService, $this->ascService, $this->customer))
				->addRule(new AppraiserNotConnectedByAscAppraiser($this->customerService, $this->ascService, $this->customer));
		});

		$binder->bind('appraiser', function(Property $property){
			$property
				->addRule(new AppraiserExists($this->appraiserService))
				->addRule(new AppraiserNotInvited($this->appraiserService, $this->customer))
				->addRule(new AppraiserNotConnected($this->appraiserService, $this->customer));
		});
	}
}
