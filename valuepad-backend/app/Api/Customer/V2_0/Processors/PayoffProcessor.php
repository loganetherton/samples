<?php
namespace ValuePad\Api\Customer\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Api\Support\Validation\Rules\MonthYearPair;
use ValuePad\Core\Appraisal\Objects\CreditCard;
use ValuePad\Core\Appraisal\Objects\Payoff;

class PayoffProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'creditCard' => 'array',
            'creditCard.firstName' => 'string',
            'creditCard.lastName' => 'string',
            'creditCard.number' => 'string',
            'creditCard.code' => 'string',
            'creditCard.expiresAt' => new MonthYearPair(),
            'creditCard.address' => 'string',
            'creditCard.city' => 'string',
            'creditCard.state' => 'string',
            'creditCard.zip' => 'string',
            'creditCard.email' => 'string',
            'creditCard.phone' => 'string',
            'amount' => 'float'
        ];
    }

    /**
     * @return Payoff
     */
    public function createPayoff()
    {
        return $this->populate(new Payoff());
    }
}
