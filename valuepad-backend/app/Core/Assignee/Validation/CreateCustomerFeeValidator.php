<?php
namespace ValuePad\Core\Assignee\Validation;

use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Validation\Rules\JobTypeAccess as AmcJobTypeAccess;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Appraiser\Validation\Rules\JobTypeAccess as AppraiserJobTypeAccess;
use ValuePad\Core\Assignee\Validation\Rules\JobTypeAvailableForCustomerFee;
use ValuePad\Core\Assignee\Services\AssigneeService;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Customer\Services\JobTypeService;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\User\Entities\User;

class CreateCustomerFeeValidator extends AbstractFeeValidator
{
    /**
     * @var JobTypeService
     */
    private $jobTypeService;

    /**
     * @var AssigneeService|AppraiserService|AmcService
     */
    private $assigneeService;

    /**
     * @var CustomerService
     */
    private $customerService;
    /**
     * @var User|Appraiser|Amc
     */
    private $assignee;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @param ContainerInterface $container
     * @param User|Appraiser|Amc $assignee
     * @param Customer $customer
     * @param string $assigneeService
     */
    public function __construct(
        ContainerInterface $container,
        User $assignee,
        Customer $customer,
        $assigneeService
    )
    {
        $this->assignee = $assignee;
        $this->customer = $customer;

        $this->jobTypeService = $container->get(JobTypeService::class);
        $this->customerService = $container->get(CustomerService::class);
        $this->assigneeService = $container->get($assigneeService);
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
                ->addRule($this->getJobTypeAccess());

            $property->addRule(new JobTypeAvailableForCustomerFee(
                $this->assigneeService,
                $this->assignee,
                $this->customer
            ));
        });

        $this->defineAmount($binder);
    }

    /**
     * @return AppraiserJobTypeAccess|AmcJobTypeAccess
     */
    private function getJobTypeAccess()
    {
        if ($this->assignee instanceof Appraiser) {
            return $this->getAppraiserJobTypeAccess();
        }

        if ($this->assignee instanceof Amc) {
            return $this->getAmcJobTypeAccess();
        }
    }

    /**
     * @return AppraiserJobTypeAccess
     */
    private function getAppraiserJobTypeAccess()
    {
        return new AppraiserJobTypeAccess(
            $this->customerService,
            $this->assigneeService,
            $this->customer,
            $this->assignee
        );
    }

    /**
     * @var AmcJobTypeAccess
     */
    private function getAmcJobTypeAccess()
    {
        return new AmcJobTypeAccess(
            $this->customerService,
            $this->assigneeService,
            $this->customer,
            $this->assignee
        );
    }
}
