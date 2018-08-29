<?php
namespace ValuePad\Core\Appraisal\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\Walk;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Support\Service\ContainerInterface;

class ReconsiderationValidator extends AbstractThrowableValidator
{
    use AdditionalDocumentValidatorTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

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
        $this->container = $container;
        $this->customer = $customer;
    }

    /**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
        $this->defineAdditionalDocument($binder, $this->container, $this->customer,
            ['namespace' => 'document']);

        $binder->bind('documents', function (Property $property) {
            $property->addRule(new Walk([$this, 'defineDocuments']));
        });

		$binder->bind('comparables', function(Property $property){
			$property->addRule(new Walk([$this, 'defineComparable']));
		});
	}

	/**
	 * @param Binder $binder
	 */
	public function defineComparable(Binder $binder)
	{
		$binder->bind('address', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank())
				->addRule(new Length(1, 255));
		});

		$binder->bind('salesPrice', function(Property $property){
			$property
				->addRule(new Greater(0));
		});

		$binder->bind('livingArea', function(Property $property){
			$property
				->addRule(new Blank())
				->addRule(new Length(1, 255));
		});

		$binder->bind('siteSize', function(Property $property){
			$property
				->addRule(new Blank())
				->addRule(new Length(1, 255));
		});

		$binder->bind('actualAge', function(Property $property){
			$property
				->addRule(new Blank())
				->addRule(new Length(1, 255));
		});

		$binder->bind('distanceToSubject', function(Property $property){
			$property
				->addRule(new Blank())
				->addRule(new Length(1, 255));
		});

		$binder->bind('sourceData', function(Property $property){
			$property
				->addRule(new Blank())
				->addRule(new Length(1, 255));
		});
	}

    /**
     * @param Binder $binder
     */
    public function defineDocuments(Binder $binder)
    {
        $this->defineAdditionalDocument($binder, $this->container, $this->customer);
    }
}
