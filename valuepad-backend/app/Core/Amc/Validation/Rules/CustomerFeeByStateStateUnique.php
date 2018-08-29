<?php
namespace ValuePad\Core\Amc\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Amc\Entities\CustomerFeeByState;
use ValuePad\Core\Amc\Services\CustomerFeeService;
use ValuePad\Core\Assignee\Entities\CustomerFee;

class CustomerFeeByStateStateUnique extends AbstractRule
{
    /**
     * @var CustomerFeeByState
     */
    private $ignoreFeeByState;

    /**
     * @var CustomerFee
     */
    private $fee;

    /**
     * @var CustomerFeeService
     */
    private $feeService;

    /**
     * @param CustomerFeeService $feeService
     * @param CustomerFee $fee
     * @param CustomerFeeByState $feeByState
     */
    public function __construct(
        CustomerFeeService $feeService,
        CustomerFee $fee,
        CustomerFeeByState $feeByState = null
    )
    {
        $this->feeService = $feeService;
        $this->ignoreFeeByState = $feeByState;
        $this->fee = $fee;
        $this->setIdentifier('already-taken');
        $this->setMessage('The provided state is already taken in the scope of the provided fee.');
    }

    /**
     * @param mixed|Value $value
     * @return Error
     */
    public function check($value)
    {
        if ($this->ignoreFeeByState && $this->ignoreFeeByState->getState()->getCode() === $value){
            return null;
        }

        if ($this->feeService->hasCustomerFeeByStateByStateCode($this->fee->getId(), $value)){
            return $this->getError();
        }

        return null;
    }
}