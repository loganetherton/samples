<?php
namespace ValuePad\Core\Payment\Objects;
use ValuePad\Core\Payment\Enums\AccountType;

class BankAccountRequisites extends AbstractRequisites
{
    /**
     * @var AccountType
     */
    private $accountType;
    public function setAccountType(AccountType $type) { $this->accountType = $type; }
    public function getAccountType() { return $this->accountType; }

    /**
     * @var string
     */
    private $routingNumber;
    public function setRoutingNumber($number) { $this->routingNumber = $number; }
    public function getRoutingNumber() { return $this->routingNumber; }

    /**
     * @var string
     */
    private $accountNumber;
    public function setAccountNumber($number) { $this->accountNumber = $number; }
    public function getAccountNumber() { return $this->accountNumber; }

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
}
