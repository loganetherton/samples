<?php
namespace ValuePad\Core\Payment\Interfaces;

use ValuePad\Core\Payment\Enums\Means;
use ValuePad\Core\Payment\Objects\Charge;
use ValuePad\Core\Payment\Objects\Purchase;
use ValuePad\Core\Payment\Objects\ReferencesTuple;
use ValuePad\Core\Payment\Objects\BankAccountRequisites;
use ValuePad\Core\Payment\Objects\CreditCardRequisites;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\Payment\Entities\ProfileReference;

interface PaymentSystemInterface
{
	/**
	 * @param User $owner
	 * @param CreditCardRequisites $requisites
	 * @return ReferencesTuple
	 */
	public function createProfileWithCreditCard(User $owner, CreditCardRequisites $requisites);

	/**
	 * @param ProfileReference $reference
	 * @param CreditCardRequisites $requisites
     * @return null|string
	 */
	public function replaceCreditCard(ProfileReference $reference, CreditCardRequisites $requisites);

    /**
     * @param User $owner
     * @param BankAccountRequisites $requisites
     * @return ReferencesTuple
     */
    public function createProfileWithBankAccount(User $owner, BankAccountRequisites $requisites);

    /**
     * @param ProfileReference $reference
     * @param BankAccountRequisites $requisites
     * @param null|string
     */
    public function replaceBankAccount(ProfileReference $reference, BankAccountRequisites $requisites);

	/**
	 * @param ProfileReference $reference
     * @param Purchase $purchase
     * @param Means $means
	 * @return Charge
	 */
	public function charge(ProfileReference $reference, Purchase $purchase, Means $means);

    /**
     * @param ProfileReference $reference
     * @param User $owner
     */
    public function refreshProfile(ProfileReference $reference, User $owner);
}
