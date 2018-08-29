<?php
namespace ValuePad\Core\Payment\Entities;

use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Payment\Enums\AccountType;
use ValuePad\Core\User\Entities\User;

class ProfileReference
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }


	/**
	 * @var User
	 */
	private $owner;
    public function setOwner(User $owner) { $this->owner = $owner; }
    public function getOwner() { return $this->owner; }

	/**
	 * @var string
	 */
	private $profileId;
    public function getProfileId() { return $this->profileId; }
    public function setProfileId($profileId) { $this->profileId = $profileId; }

	/**
	 * @var string
	 */
	private $creditCardProfileId;
	public function getCreditCardProfileId() { return $this->creditCardProfileId; }
	public function setCreditCardProfileId($profileId) { $this->creditCardProfileId = $profileId; }

    /**
     * @var string
     */
    private $bankAccountProfileId;
    public function getBankAccountProfileId() { return $this->bankAccountProfileId; }
    public function setBankAccountProfileId($profileId) { $this->bankAccountProfileId = $profileId; }


    /**
     * @var string
     */
    private $maskedCreditCardNumber;
    public function setMaskedCreditCardNumber($number) { $this->maskedCreditCardNumber = $number; }
    public function getMaskedCreditCardNumber() { return $this->maskedCreditCardNumber; }

    /**
     * @var string
     */
    private $maskedRoutingNumber;
    public function setMaskedRoutingNumber($number) { $this->maskedRoutingNumber = $number; }
    public function getMaskedRoutingNumber() { return $this->maskedRoutingNumber; }

    /**
     * @var string
     */
    private $maskedAccountNumber;
    public function setMaskedAccountNumber($number) { $this->maskedAccountNumber = $number; }
    public function getMaskedAccountNumber() { return $this->maskedAccountNumber; }

    /**
     * @var AccountType
     */
    private $accountType;
    public function setAccountType(AccountType $type) { $this->accountType = $type; }
    public function getAccountType() { return $this->accountType; }

    /**
     * @var string
     */
    private $nameOnAccount;
    public function setNameOnAccount($name) { $this->nameOnAccount = $name; }
    public function getNameOnAccount() { return $this->nameOnAccount; }

    /**
     * @var string
     */
    private $bankName;
    public function setBankName($name) { $this->bankName = $name; }
    public function getBankName() { return $this->bankName; }

    /**
     * @var string
     */
    private $address;
    public function setAddress($address) { $this->address = $address; }
    public function getAddress() { return $this->address; }

    /**
     * @var string
     */
    private $city;
    public function setCity($city) { $this->city = $city; }
    public function getCity() { return $this->city; }

    /**
     * @var string
     */
    private $zip;
    public function setZip($zip) { $this->zip = $zip; }
    public function getZip() { return $this->zip; }

    /**
     * @var State
     */
    private $state;
    public function setState(State $state) { $this->state = $state; }
    public function getState()  { return $this->state; }
}
