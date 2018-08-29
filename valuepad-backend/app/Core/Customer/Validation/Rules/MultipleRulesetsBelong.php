<?php
namespace ValuePad\Core\Customer\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;

class MultipleRulesetsBelong extends AbstractRule
{
    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @param CustomerService $customerService
     * @param Customer $customer
     */
    public function __construct(CustomerService $customerService, Customer $customer)
    {
        $this->customerService = $customerService;
        $this->customer = $customer;

        $this->setIdentifier('not-belong');
        $this->setMessage('One of the provided rulesets does not belong to the specified customer.');
    }

    /**
     * @param array $value
     * @return Error|null
     */
    public function check($value)
    {
        if (count($value) === 0){
            return null;
        }

        if (!$this->customerService->hasRulesets($this->customer->getId(), $value)){
            return $this->getError();
        }

        return null;
    }
}
