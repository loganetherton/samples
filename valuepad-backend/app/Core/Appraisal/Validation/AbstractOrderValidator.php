<?php
namespace ValuePad\Core\Appraisal\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Alphanumeric;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Less;
use Ascope\Libraries\Validation\Rules\NotClearable;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\Unique;
use Ascope\Libraries\Validation\Rules\Walk;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Validation\Rules\ClientBelong;
use ValuePad\Core\Appraisal\Validation\Rules\DocumentWalk;
use ValuePad\Core\Appraisal\Validation\Rules\FhaNumber;
use ValuePad\Core\Appraisal\Validation\Rules\InspectionDate;
use ValuePad\Core\Appraisal\Validation\Rules\UniqueContactType;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Enums\Criticality;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Customer\Validation\Rules\JobTypeBelongs;
use ValuePad\Core\Customer\Validation\Rules\MultipleJobTypesBelong;
use ValuePad\Core\Customer\Validation\Rules\MultipleRulesetsBelong;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Definer\LocationDefiner;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Support\Service\ContainerInterface;
use DateTime;
use ValuePad\Core\Shared\Validation\Rules\Phone;


abstract class AbstractOrderValidator extends AbstractThrowableValidator
{
	use ConditionsValidatorTrait;

	private $isBidRequest;

	/**
	 * @var StateService
	 */
	private $stateService;

	/**
	 * @var CustomerService
	 */
	private $customerService;

	/**
	 * @var Customer
	 */
	protected $customer;

	/**
	 * @var Order
	 */
	protected $existingOrder;

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var bool
	 */
	protected $isPaidClearable = true;

    /**
     * @var EnvironmentInterface
     */
    protected $environment;

	/**
	 * @param ContainerInterface $container
	 * @param Customer $customer
	 * @param bool $isBidRequest
	 */
	public function __construct(ContainerInterface $container, Customer $customer, $isBidRequest = false)
	{
		$this->stateService = $container->get(StateService::class);
		$this->customerService = $container->get(CustomerService::class);
		$this->isBidRequest = $isBidRequest;
		$this->customer = $customer;
		$this->container = $container;
        $this->environment = $container->get(EnvironmentInterface::class);
	}

	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$constraints = new Constraints();

		$binder->bind('fileNumber', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank())
				->addRule(new Length(1, 40));
		});

        $binder->bind('intendedUse', function(Property $property){
            $property
                ->addRule(new Length(1, 255))
                ->addRule(new Blank());
        });

		$binder->bind('referenceNumber', function(Property $property){
			$property
				->addRule(new Length(1, 255))
				->addRule(new Blank());
		});

		$binder->bind('client', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new ClientBelong($this->customerService, $this->customer));
		});

		$binder->bind('clientDisplayedOnReport', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new ClientBelong($this->customerService, $this->customer));
		});

		$binder->bind('amcLicenseNumber', function(Property $property){
			$property
				->addRule(new Blank());
		});

		if (!$this->isRelaxed()){
			$binder->bind('amcLicenseExpiresAt', function(Property $property){
				$property
					->addRule(new Greater(new DateTime(), false));
			});
		}

		$jobTypeBelongs = new JobTypeBelongs($this->customerService, $this->customer, $this->isRelaxed());

		if ($this->existingOrder){
			$jobTypeBelongs->setTrustedJobType($this->existingOrder->getJobType());
		}

		$binder->bind('jobType', function(Property $property) use ($jobTypeBelongs){
			$property->addRule(new Obligate());
			$property->addRule($jobTypeBelongs);
		});

		$binder->bind('additionalJobTypes', function(Property $property){
			$property
				->addRule(new Unique());

			$property->addRule(new MultipleJobTypesBelong($this->customerService, $this->customer, $this->isRelaxed()));
		});


		$binder->bind('rulesets', function(Property $property){
			$property
				->addRule(new Unique())
				->addRule(new MultipleRulesetsBelong($this->customerService, $this->customer));
		});

		$binder->bind('fee', function(Property $property){
			if (!$this->isBidRequest){
				$property->addRule(new Obligate());
			}
			$property->addRule(new Greater(0));
		});

		$binder->bind('techFee', function(Property $property){
			$property
				->addRule(new Greater(0));
		});

		$binder->bind('purchasePrice', function(Property $property){
			$property
				->addRule(new Greater(0));
		});

		if (!$this->isPaidClearable){
			$binder->bind('isPaid', function(Property $property){
				$property
					->addRule(new NotClearable());
			});
		}

		$binder->bind('paidAt', function(Property $property){
			$property->addRule(new Obligate());
		})->when($constraints->are([Constraints::IS_PAID_EQUALS_TRUE]));

        if ($this->existingOrder){
            $binder->bind('inspectionScheduledAt', function(Property $property){
                $property->addRule(new Obligate());
            })->when(function(){
                return $this->existingOrder->getWorkflow()->has(new ProcessStatus(ProcessStatus::INSPECTION_SCHEDULED));
            });

            $binder->bind('inspectionCompletedAt', function(Property $property){
                $property->addRule(new Obligate());
            })->when(function(){
                return $this->existingOrder->getWorkflow()->has(new ProcessStatus(ProcessStatus::INSPECTION_COMPLETED));
            });

            $binder->bind('estimatedCompletionDate', function(Property $property){
                $property->addRule(new Obligate());
            })->when(function(){
                return $this->existingOrder->getWorkflow()->has(new ProcessStatus(ProcessStatus::INSPECTION_SCHEDULED))
                    || $this->existingOrder->getWorkflow()->has(new ProcessStatus(ProcessStatus::INSPECTION_COMPLETED));
            });
        }

        if (!$this->environment->isRelaxed()
            && $this->customer->getSettings()->getPreventViolationOfDateRestrictions()->is(Criticality::HARDSTOP)){

            $binder->bind('inspectionScheduledAt', ['dueDate', 'inspectionScheduledAt'], function(Property $property){
                $property
                    ->addRule(new InspectionDate($this->customer->getSettings()->getDaysPriorInspectionDate()));
            });

            $binder->bind('inspectionCompletedAt', ['dueDate', 'inspectionCompletedAt'], function(Property $property){
                $property
                    ->addRule(new InspectionDate($this->customer->getSettings()->getDaysPriorInspectionDate()));
            });

            $binder->bind('estimatedCompletionDate', ['dueDate', 'estimatedCompletionDate'], function(Property $property){
                $property
                    ->addRule(new InspectionDate($this->customer->getSettings()->getDaysPriorEstimatedCompletionDate()));
            });
        }

		$binder->bind('fhaNumber', function(Property $property){
			$property
				->addRule(new FhaNumber())
				->addRule(new Length(1, 100));
		});

		$binder->bind('loanNumber', function(Property $property){
			$property
				->addRule(new Blank())
				->addRule(new Length(1, 40));
		});

		$binder->bind('loanType', function(Property $property){
			$property->addRule(new Blank());
		});

        $binder->bind('loanAmount', function (Property $property) {
            $property->addRule(new Greater(0));
        });

		$binder->bind('salesPrice', function(Property $property){
			$property->addRule(new Greater(0));
		});

		$binder->bind('concession', function(Property $property){
			$property->addRule(new Greater(0));
		});

		$binder->bind('orderedAt', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Less(new DateTime('+5 seconds')));
		});

        $binder
            ->bind('fdic.fin', function(Property $property){
                $property
                    ->addRule(new Blank())
                    ->addRule(new Length(1, 5))
                    ->addRule(new Alphanumeric());
            });

        $binder
            ->bind('fdic.taskOrder', function(Property $property){
                $property
                    ->addRule(new Blank())
                    ->addRule(new Length(1, 4))
                    ->addRule(new Alphanumeric());
            });

        $binder
            ->bind('fdic.assetNumber', function(Property $property){
                $property
                    ->addRule(new Blank())
                    ->addRule(new Length(1, 12))
                    ->addRule(new Alphanumeric());
            });

        $binder
            ->bind('fdic.line', function(Property $property){
                $property
                    ->addRule(new Greater(1))
                    ->addRule(new Less(3));
            });

        $binder
            ->bind('fdic.contractor', function(Property $property){
                $property
                    ->addRule(new Blank())
                    ->addRule(new Length(1, 30));
            });

		$binder->bind('property.type', function(Property $property){
			$property->addRule(new Blank());
		});

		$fields = [
			'approxBuildingSize',
			'approxLandSize',
			'buildingAge',
			'numberOfStories',
			'numberOfUnits',
			'grossRentalIncome',
			'incomeSalesCost'
		];

		foreach ($fields as $field){
			$binder->bind('property.'.$field, function(Property $property){
				$property->addRule(new Greater(0, false));
			});
		}

        (new LocationDefiner($this->stateService))
            ->setHolder('property')
            ->withCounty(true)
            ->define($binder);

		$binder->bind('property.occupancy', function(Property $property){
			$property
				->addRule(new Obligate());
		});

		$binder->bind('property.bestPersonToContact', function(Property $property){
			$property
				->addRule(new Obligate());
		});

		$binder->bind('property.contacts', function(Property $property){

			$property->addRule(new UniqueContactType());

			$property->addRule(new Walk(function(Binder $binder){
				$binder->bind('type', function(Property $property){
					$property
						->addRule(new Obligate());
				});

				foreach (['name', 'firstName', 'lastName', 'middleName'] as $field){
					$binder->bind($field, function(Property $property) use ($field){
						$property
							->addRule(new Blank());
					});
				}

				foreach (['workPhone', 'homePhone', 'cellPhone'] as $field){
					$binder->bind($field, function(Property $property) use ($field){
						$property
							->addRule(new Phone());
					});
				}

				$binder->bind('email', function(Property $property){
					$property
						->addRule(new Length(1, 255));
				});
			}));
		});

		$binder->bind('property.additionalComments', function(Property $property){
			$property->addRule(new Blank());
		});

		$binder->bind('property.legal', function(Property $property){
			$property->addRule(new Blank());
		});

		$binder->bind('instructionDocuments', function(Property $property){
			$property->addRule(new DocumentWalk());
		});

		$binder->bind('additionalDocuments', function(Property $property){
			$property->addRule(new DocumentWalk());
		});

		$binder->bind('instruction', function(Property $property){
			$property->addRule(new Blank());
		});

		$this->defineConditions($binder, ['namespace' => 'acceptedConditions']);

		$binder->bind('acceptedConditions.additionalComments', function(Property $property){
			$property->addRule(new Length(1, 255));
		});
	}

	/**
	 * @return bool
	 */
	private function isRelaxed()
	{
		/**
		 * @var EnvironmentInterface $environment
		 */
		$environment = $this->container->get(EnvironmentInterface::class);

		return $environment->isRelaxed();
	}
}
