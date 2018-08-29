<?php
namespace ValuePad\Core\Assignee\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Assignee\Services\AssigneeService;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\User\Entities\User;

class JobTypeAvailableForCustomerFee extends AbstractRule
{
    /**
     * @var AssigneeService|AppraiserService|AmcService
     */
    private $assigneeService;

    /**
     * @var User|Appraiser|Amc
     */
    private $assignee;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @param AssigneeService|AppraiserService|AmcService $assigneeService
     * @param User|Appraiser|Amc $assignee
     * @param Customer $customer
     */
    public function __construct(AssigneeService $assigneeService, User $assignee, Customer $customer)
    {
        $this->assigneeService = $assigneeService;
        $this->assignee = $assignee;
        $this->customer = $customer;

        $this->setIdentifier('already-taken');
        $this->setMessage('A customer fee has been already set for the provided job type.');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        $taken = $this->assigneeService->hasCustomerFeeWithJobType(
            $this->assignee->getId(),
            $this->customer->getId(),
            $value
        );

        if ($taken){
            return $this->getError();
        }

        return null;
    }
}
