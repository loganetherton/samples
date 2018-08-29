<?php
namespace ValuePad\Core\Payment\Services;

use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Payment\Entities\Transaction;
use ValuePad\Core\Payment\Enums\Means;
use ValuePad\Core\Payment\Enums\Status;
use ValuePad\Core\Payment\Entities\ProfileReference;
use ValuePad\Core\Payment\Exceptions\ChargeDeclinedException;
use ValuePad\Core\Payment\Exceptions\ChargeErrorException;
use ValuePad\Core\Payment\Exceptions\ChargePendingException;
use ValuePad\Core\Payment\Interfaces\PaymentSystemInterface;
use ValuePad\Core\Payment\Objects\AbstractPaymentMethod;
use ValuePad\Core\Payment\Objects\AbstractRequisites;
use ValuePad\Core\Payment\Objects\BankAccount;
use ValuePad\Core\Payment\Objects\Charge;
use ValuePad\Core\Payment\Objects\CreditCard;
use ValuePad\Core\Payment\Objects\BankAccountRequisites;
use ValuePad\Core\Payment\Objects\CreditCardRequisites;
use ValuePad\Core\Payment\Objects\Purchase;
use ValuePad\Core\Payment\Validation\BankAccountValidator;
use ValuePad\Core\Payment\Validation\CreditCardValidator;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\User\Entities\User;
use Exception;
use DateTime;
use ValuePad\Core\User\Interfaces\LocationAwareInterface;

class PaymentService extends AbstractService
{
	/**
	 * @var PaymentSystemInterface
	 */
	private $paymentSystem;

	/**
	 * @param PaymentSystemInterface $paymentSystem
	 */
	public function initialize(PaymentSystemInterface $paymentSystem)
	{
		$this->paymentSystem = $paymentSystem;
	}

	/**
	 * @param int $ownerId
     * @param Purchase $purchase
     * @param Means $means
	 */
	public function charge($ownerId, Purchase $purchase, Means $means)
	{
		/**
		 * @var ProfileReference $reference
		 */
		$reference = $this->entityManager
			->getRepository(ProfileReference::class)
			->findOneBy(['owner' => $ownerId]);

		if ($reference === null){
			throw new PresentableException('None of the supported payment methods has been provided.');
		}

		if ($means->is(Means::CREDIT_CARD) && $reference->getCreditCardProfileId() === null){
            throw new PresentableException('Credit card details have not been provided.');
        }

        if ($means->is(Means::BANK_ACCOUNT) && $reference->getBankAccountProfileId() === null){
            throw new PresentableException('Bank account details have not been provided.');
        }

		$charge = $this->paymentSystem->charge($reference, $purchase, $means);

        $transaction = new Transaction();

        /**
         * @var User $owner
         */
        $owner = $this->entityManager->getReference(User::class, $ownerId);

        $transaction->setOwner($owner);
        $transaction->setExternalId($charge->getTransactionId());
        $transaction->setMessage($charge->getMessage());
        $transaction->setStatus($charge->getStatus());

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

		$this->throwChargeExceptionIfNeeded($charge);
	}

	/**
	 * @param Charge $charge
	 */
	private function throwChargeExceptionIfNeeded(Charge $charge)
	{
		if ($charge->getStatus()->is(Status::APPROVED)){
			return ;
		}

		if ($charge->getStatus()->is(Status::DECLINED)){
			throw new ChargeDeclinedException($charge->getMessage());
		}

		if ($charge->getStatus()->is(Status::PENDING)){
			throw new ChargePendingException($charge->getMessage());
		}


		if ($charge->getStatus()->is(Status::ERROR)){
			throw new ChargeErrorException($charge->getMessage());
		}
	}

	/**
	 * @param int $ownerId
	 * @param CreditCardRequisites $requisites
     * @return CreditCard
     * @throws Exception
	 */
	public function switchCreditCard($ownerId, CreditCardRequisites $requisites)
	{
        /**
         * @var User $owner
         */
        $owner = $this->entityManager->getReference(User::class, $ownerId);

		(new CreditCardValidator($this->container->get(StateService::class), $owner))->validate($requisites);

        if ($owner instanceof LocationAwareInterface){
            $this->populateRequisitesWithLocation($owner, $requisites);
        }

		/**
		 * @var ProfileReference $reference
		 */
		$reference = $this->entityManager
			->getRepository(ProfileReference::class)
			->findOneBy(['owner' => $owner->getId()]);

        if ($reference === null){

            $reference = new ProfileReference();
            $reference->setOwner($owner);

            $tuple = $this->paymentSystem->createProfileWithCreditCard($owner, $requisites);

            $reference->setCreditCardProfileId($tuple->getPaymentProfileId());
            $reference->setProfileId($tuple->getProfileId());

            $this->entityManager->persist($reference);

        } else {
            if ($creditCardProfileId = $this->paymentSystem->replaceCreditCard($reference, $requisites)){
                $reference->setCreditCardProfileId($creditCardProfileId);
            }
        }

        $reference->setMaskedCreditCardNumber(substr($requisites->getNumber(), -4, 4));

        $this->populateReferenceWithLocation($reference, $requisites);

        $this->entityManager->flush();

		return $this->createCreditCard($reference);
	}

    /**
     * @param int $ownerId
     * @param BankAccountRequisites $requisites
     * @return BankAccount
     */
	public function changeBankAccount($ownerId, BankAccountRequisites $requisites)
    {
        /**
         * @var User $owner
         */
        $owner = $this->entityManager->getReference(User::class, $ownerId);

        (new BankAccountValidator($this->container->get(StateService::class), $owner))->validate($requisites);

        if ($owner instanceof LocationAwareInterface){
            $this->populateRequisitesWithLocation($owner, $requisites);
        }

        /**
         * @var ProfileReference $reference
         */
        $reference = $this->entityManager
            ->getRepository(ProfileReference::class)
            ->findOneBy(['owner' => $owner->getId()]);

        if ($reference === null){

            $reference = new ProfileReference();
            $reference->setOwner($owner);

            $tuple = $this->paymentSystem->createProfileWithBankAccount($owner, $requisites);

            $reference->setBankAccountProfileId($tuple->getPaymentProfileId());
            $reference->setProfileId($tuple->getProfileId());

            $this->entityManager->persist($reference);

        } else {
            if ($bankAccountProfileId = $this->paymentSystem->replaceBankAccount($reference, $requisites)){
                $reference->setBankAccountProfileId($bankAccountProfileId);
            }
        }

        $reference->setMaskedAccountNumber(substr($requisites->getAccountNumber(), -4, 4));
        $reference->setMaskedRoutingNumber(substr($requisites->getRoutingNumber(), -4, 4));
        $reference->setAccountType($requisites->getAccountType());
        $reference->setBankName($requisites->getBankName());
        $reference->setNameOnAccount($requisites->getNameOnAccount());

        $this->populateReferenceWithLocation($reference, $requisites);

        $this->entityManager->flush();

        return $this->createBankAccount($reference);
    }

    /**
     * @param ProfileReference $reference
     * @param AbstractRequisites $requisites
     */
    private function populateReferenceWithLocation(ProfileReference $reference, AbstractRequisites $requisites)
    {
        $reference->setAddress($requisites->getAddress());
        $reference->setCity($requisites->getCity());
        $reference->setZip($requisites->getZip());

        /**
         * @var State $state
         */
        $state = $this->entityManager->getReference(State::class, $requisites->getState());

        $reference->setState($state);
    }

    /**
     * @param ProfileReference $reference
     * @return BankAccount
     */
    private function createBankAccount(ProfileReference $reference)
    {
        $bankAccount = new BankAccount();

        $bankAccount->setAccountNumber($reference->getMaskedAccountNumber());
        $bankAccount->setRoutingNumber($reference->getMaskedRoutingNumber());
        $bankAccount->setAccountType($reference->getAccountType());
        $bankAccount->setBankName($reference->getBankName());
        $bankAccount->setNameOnAccount($reference->getNameOnAccount());

        $this->populatePaymentMethodWithLocation($bankAccount, $reference);

        return $bankAccount;
    }

    /**
     * @param ProfileReference $reference
     * @return CreditCard
     */
    private function createCreditCard(ProfileReference $reference)
    {
        $cc = new CreditCard();

        $cc->setNumber($reference->getMaskedCreditCardNumber());

        $this->populatePaymentMethodWithLocation($cc, $reference);

        return $cc;
    }

    /**
     * @param AbstractPaymentMethod $method
     * @param ProfileReference $reference
     */
    private function populatePaymentMethodWithLocation(AbstractPaymentMethod $method, ProfileReference $reference)
    {
        $method->setAddress($reference->getAddress());
        $method->setCity($reference->getCity());
        $method->setZip($reference->getZip());
        $method->setState($reference->getState());
    }

    /**
     * @param LocationAwareInterface $owner
     * @param AbstractRequisites $requisites
     */
    private function populateRequisitesWithLocation(LocationAwareInterface $owner, AbstractRequisites $requisites)
    {
        // In case if the provided address is not fully completed  we take the whole address from the owner

        if (!$requisites->getAddress()
            || !$requisites->getState()
            || !$requisites->getCity()
            || !$requisites->getZip()){
            $requisites->setAddress($owner->getAddress1());
            $requisites->setCity($owner->getCity());
            $requisites->setState($owner->getState()->getCode());
            $requisites->setZip($owner->getZip());
        }
    }

    /**
     * @param int $ownerId
     * @return BankAccount
     */
    public function getBankAccount($ownerId)
    {
        /**
         * @var ProfileReference $reference
         */
        $reference = $this->entityManager->getRepository(ProfileReference::class)
            ->findOneBy(['owner' => $ownerId]);

        if ($reference === null || $reference->getBankAccountProfileId() === null){
            return null;
        }

        return $this->createBankAccount($reference);
    }


	/**
	 * @param int $ownerId
	 * @return CreditCard
	 */
	public function getCreditCard($ownerId)
	{
		/**
		 * @var ProfileReference $reference
		 */
		$reference = $this->entityManager->getRepository(ProfileReference::class)
			->findOneBy(['owner' => $ownerId]);

		if ($reference === null || $reference->getCreditCardProfileId() === null){
			return null;
		}

		return $this->createCreditCard($reference);
    }

    /**
     * @param int $ownerId
     * @return bool
     */
    public function hasCreditCard($ownerId)
    {
        return $this->getCreditCard($ownerId) !== null ? true : false;
    }

    /**
     * @param int $ownerId
     */
	public function refreshProfile($ownerId)
    {
        /**
         * @var ProfileReference $reference
         */
        $reference = $this->entityManager->getRepository(ProfileReference::class)
            ->findOneBy(['owner' => $ownerId]);

        if ($reference === null){
            return ;
        }

        /**
         * @var User $owner
         */
        $owner = $this->entityManager->find(User::class, $ownerId);

        $this->paymentSystem->refreshProfile($reference, $owner);
    }

    public function deleteOldTransactions()
    {
        $this->entityManager
            ->getRepository(Transaction::class)
            ->delete(['createdAt' => ['<', new DateTime('-1 month')]]);
    }
}
