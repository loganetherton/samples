<?php
namespace ValuePad\Core\Amc\Services;

use ValuePad\Core\Amc\Entities\CustomerFeeByState;
use ValuePad\Core\Amc\Entities\FeeByState;
use ValuePad\Core\Amc\Validation\CustomerFeeByStateValidator;
use ValuePad\Core\Assignee\Entities\CustomerFee;

class CustomerFeeByStateService extends AbstractFeeByStateService
{
    use UseCustomerFeeByStateTrait;
    use UseCustomerFeeTrait;

    /**
     * @return CustomerFeeByStateValidator
     */
    protected function getValidator()
    {
        return new CustomerFeeByStateValidator($this->container);
    }

    /**
     * @param CustomerFee $customerFee
     * @param FeeByState $feeByState
     * @param bool $flush When set to false, the unit of work won't be committed
     * @return CustomerFeeByState
     */
    public function makeWithDefaultStateFee(CustomerFee $customerFee, FeeByState $feeByState, $flush = false)
    {
        $customerFeeByState = new CustomerFeeByState();
        $customerFeeByState->setAmount($feeByState->getAmount());
        $customerFeeByState->setState($feeByState->getState());
        $customerFeeByState->setFee($customerFee);

        $this->entityManager->persist($customerFeeByState);

        if ($flush) {
            $this->entityManager->flush();
        }

        return $customerFeeByState;
    }
}
