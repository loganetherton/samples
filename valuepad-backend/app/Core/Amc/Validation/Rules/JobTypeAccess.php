<?php
namespace ValuePad\Core\Amc\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;

class JobTypeAccess extends AbstractRule
{
    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var AmcService
     */
    private $amcService;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Amc
     */
    private $amc;

    /**
     * @param CustomerService $customerService
     * @param AmcService $amcService
     * @param Customer $customer
     * @param Amc $amc
     */
    public function __construct(
        CustomerService $customerService,
        AmcService $amcService,
        Customer $customer,
        Amc $amc
    )
    {
        $this->customerService = $customerService;
        $this->amcService = $amcService;
        $this->customer = $customer;
        $this->amc = $amc;

        $this->setIdentifier('access');
        $this->setMessage('Unable to proceed with the provided job type.');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        if (!$this->customerService->hasPayableJobType($this->customer->getId(), $value)){
            return $this->getError();
        }

        if (!$this->amcService->isRelatedWithCustomer($this->amc->getId(), $this->customer->getId())){
            return $this->getError();
        }

        return null;
    }
}
