<?php
namespace ValuePad\Core\Customer\Interfaces;
use ValuePad\Core\Customer\Objects\PayoffCreditCardRequisites;
use ValuePad\Core\Customer\Objects\PayoffPurchase;

interface WalletInterface
{
    /**
     * @param PayoffCreditCardRequisites $requisites
     * @param PayoffPurchase $purchase
     */
    public function pay(PayoffCreditCardRequisites $requisites, PayoffPurchase $purchase);
}
