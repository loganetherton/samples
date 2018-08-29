<?php
namespace ValuePad\Core\Appraisal\Objects;
use ValuePad\Core\Customer\Objects\PayoffCreditCardRequisites;

class Payoff
{
    /**
     * @var float
     */
    private $amount;
    public function setAmount($amount) { $this->amount = $amount; }
    public function getAmount() { return $this->amount; }

    /**
     * @var PayoffCreditCardRequisites
     */
    private $creditCard;
    public function setCreditCard(PayoffCreditCardRequisites $requisites) { $this->creditCard = $requisites; }
    public function getCreditCard() { return $this->creditCard; }
}
