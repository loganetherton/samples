<?php
namespace ValuePad\Core\Appraisal\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;

class ClientBelong extends AbstractRule
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

        $this->setMessage('The provided client does not belong to the provided customer.');
        $this->setIdentifier('not-belong');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        if (!$this->customerService->hasClient($this->customer->getId(), $value)){
            return $this->getError();
        }

        return null;
    }
}
