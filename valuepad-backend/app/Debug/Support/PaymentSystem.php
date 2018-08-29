<?php
namespace ValuePad\Debug\Support;

use ValuePad\Core\Payment\Entities\ProfileReference;
use ValuePad\Core\Payment\Enums\Status;
use ValuePad\Core\Payment\Interfaces\PaymentSystemInterface;
use ValuePad\Core\Payment\Objects\Charge;
use ValuePad\Core\Payment\Objects\Purchase;
use ValuePad\Core\Payment\Objects\ReferencesTuple;
use ValuePad\Core\Payment\Objects\BankAccountRequisites;
use ValuePad\Core\Payment\Objects\CreditCardRequisites;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\Payment\Enums\Means;

class PaymentSystem implements PaymentSystemInterface
{
	/**
	 * @var Storage
	 */
	private $storage;

	public function __construct()
	{
		$this->storage = new Storage('payment.json');
	}

	/**
	 * @param User $owner
	 * @param CreditCardRequisites $requisites
	 * @return ReferencesTuple
	 */
	public function createProfileWithCreditCard(User $owner, CreditCardRequisites $requisites)
	{
		return $this->createProfileWithPaymentProfile($owner, Means::CREDIT_CARD, [
            'number' => $requisites->getNumber()
        ]);
	}

    /**
     * @param User $owner
     * @param BankAccountRequisites $requisites
     * @return ReferencesTuple
     */
    public function createProfileWithBankAccount(User $owner, BankAccountRequisites $requisites)
    {
        return $this->createProfileWithPaymentProfile($owner,  Means::BANK_ACCOUNT, [
            'accountType' => (string) $requisites->getAccountType(),
            'routingNumber' => $requisites->getRoutingNumber(),
            'accountNumber' => $requisites->getAccountNumber(),
            'nameOnAccount' => $requisites->getNameOnAccount(),
            'bankName' => $requisites->getBankName()
        ]);
    }

    /**
     * @param User $owner
     * @param string $means
     * @param array $data
     * @return ReferencesTuple
     */
    private function createProfileWithPaymentProfile(User $owner, $means, array $data)
    {
        $profileId = uniqid();
        $data['id'] = $paymentProfileId = uniqid();

        if ($this->storage->size() > 100){
            $this->storage->drop();
        }

        $this->storage->store([
            'owner' => $owner->getId(),
            'id' => $profileId,
            $means => $data
        ]);

        return new ReferencesTuple($profileId, $paymentProfileId);

    }

	/**
	 * @param ProfileReference $reference
	 * @param CreditCardRequisites $requisites
     * @return string|null
	 */
	public function replaceCreditCard(ProfileReference $reference, CreditCardRequisites $requisites)
	{
        if ($reference->getCreditCardProfileId() === null){
            $id = uniqid();
            $modifier = function($row) use ($id, $requisites){
                $row[Means::CREDIT_CARD] = [
                    'id' => $id,
                    'number' => $requisites->getNumber()
                ];

                return $row;
            };
        } else {
            $id = null;
            $modifier = function($row) use ($requisites){
                $row[Means::CREDIT_CARD]['number'] = $requisites->getNumber();

                return $row;
            };
        }

        $this->storage->replace($modifier,
            function($row) use ($reference){
                return $row['id'] == $reference->getProfileId();
            }
        );

        return $id;
	}

	/**
	 * @param ProfileReference $reference
	 * @param Purchase $purchase
     * @param Means $means
	 * @return Charge
	 */
	public function charge(ProfileReference $reference, Purchase $purchase, Means $means)
	{
		$charge = new Charge();
		$charge->setTransactionId(uniqid());
		$charge->setMessage('Some message');
		$charge->setStatus(new Status(Status::APPROVED));

		return $charge;
	}

    /**
     * @param ProfileReference $reference
     * @param BankAccountRequisites $requisites
     * @return string|null
     */
    public function replaceBankAccount(ProfileReference $reference, BankAccountRequisites $requisites)
    {
        if ($reference->getBankAccountProfileId() === null){
            $id = uniqid();
            $modifier = function($row) use ($id, $requisites){
                $row[Means::BANK_ACCOUNT] = [
                    'id' => $id,
                    'accountType' => (string) $requisites->getAccountType(),
                    'routingNumber' => $requisites->getRoutingNumber(),
                    'accountNumber' => $requisites->getAccountNumber(),
                    'nameOnAccount' => $requisites->getNameOnAccount(),
                    'bankName' => $requisites->getBankName()
                ];

                return $row;
            };
        } else {
            $id = null;
            $modifier = function($row) use ($requisites){
                $row[Means::BANK_ACCOUNT]['accountType'] = (string) $requisites->getAccountType();
                $row[Means::BANK_ACCOUNT]['routingNumber'] = $requisites->getRoutingNumber();
                $row[Means::BANK_ACCOUNT]['accountNumber'] = $requisites->getAccountNumber();
                $row[Means::BANK_ACCOUNT]['nameOnAccount'] = $requisites->getNameOnAccount();
                $row[Means::BANK_ACCOUNT]['bankName'] = $requisites->getBankName();

                return $row;
            };
        }

        $this->storage->replace($modifier,
            function($row) use ($reference){
                return $row['id'] == $reference->getProfileId();
            }
        );

        return $id;
    }

    /**
     * @param ProfileReference $reference
     * @param User $owner
     */
    public function refreshProfile(ProfileReference $reference, User $owner)
    {
        //
    }
}
