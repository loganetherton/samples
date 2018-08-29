<?php
namespace ValuePad\DAL\Payment\Support;

use Ascope\Libraries\Validation\PresentableException;
use net\authorize\api\contract\v1\BankAccountType;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileResponse;
use net\authorize\api\contract\v1\CreateTransactionRequest;
use net\authorize\api\contract\v1\CreateTransactionResponse;
use net\authorize\api\contract\v1\CustomerAddressType;
use net\authorize\api\contract\v1\CustomerProfileBaseType;
use net\authorize\api\contract\v1\CustomerProfileExType;
use net\authorize\api\contract\v1\CustomerProfilePaymentType;
use net\authorize\api\contract\v1\LineItemType;
use net\authorize\api\contract\v1\OrderType;
use net\authorize\api\contract\v1\PaymentProfileType;
use net\authorize\api\contract\v1\TransactionRequestType;
use net\authorize\api\contract\v1\TransactionResponseType;
use net\authorize\api\contract\v1\UpdateCustomerPaymentProfileResponse;
use net\authorize\api\contract\v1\UpdateCustomerProfileRequest;
use net\authorize\api\controller\CreateCustomerPaymentProfileController;
use net\authorize\api\controller\CreateTransactionController;
use net\authorize\api\controller\UpdateCustomerProfileController;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\Invoice;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Payment\Entities\ProfileReference;
use ValuePad\Core\Payment\Enums\Means;
use ValuePad\Core\Payment\Enums\Status;
use ValuePad\Core\Payment\Interfaces\PaymentSystemInterface;
use ValuePad\Core\Payment\Objects\AbstractRequisites;
use ValuePad\Core\Payment\Objects\Charge;
use ValuePad\Core\Payment\Objects\Purchase;
use ValuePad\Core\Payment\Objects\ReferencesTuple;
use ValuePad\Core\Payment\Objects\BankAccountRequisites;
use ValuePad\Core\Payment\Objects\CreditCardRequisites;
use ValuePad\Core\User\Entities\User;
use net\authorize\api\contract\v1\ANetApiResponseType;
use net\authorize\api\contract\v1\CreateCustomerProfileRequest;
use net\authorize\api\contract\v1\CreateCustomerProfileResponse;
use net\authorize\api\contract\v1\CreditCardType;
use net\authorize\api\contract\v1\CustomerPaymentProfileExType;
use net\authorize\api\contract\v1\CustomerPaymentProfileType;
use net\authorize\api\contract\v1\CustomerProfileType;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\contract\v1\PaymentType;
use net\authorize\api\contract\v1\UpdateCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\UpdateCustomerProfileResponse;
use net\authorize\api\controller\CreateCustomerProfileController;
use net\authorize\api\controller\UpdateCustomerPaymentProfileController;
use Illuminate\Contracts\Config\Repository as Config;
use RuntimeException;
use ValuePad\Core\User\Interfaces\BusinessInterface;
use ValuePad\Core\User\Interfaces\EmailHolderInterface;
use ValuePad\Core\User\Interfaces\FaxHolderInterface;
use ValuePad\Core\User\Interfaces\IndividualInterface;
use ValuePad\Core\User\Interfaces\PhoneHolderInterface;
use net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType;

class PaymentSystem implements PaymentSystemInterface
{
	/**
	 * @var array
	 */
	private $config;

	/**
	 * @param  Config $config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config->get('app.authorize_net', []);
	}

	/**
	 * @param User $owner
	 * @param CreditCardRequisites $requisites
	 * @return ReferencesTuple
	 */
	public function createProfileWithCreditCard(User $owner, CreditCardRequisites $requisites)
	{
		return $this->createProfileWithPaymentProfile($owner, $this->createCreditCardProfile($owner, $requisites));
	}

    /**
     * @param User $owner
     * @param BankAccountRequisites $requisites
     * @return ReferencesTuple
     */
    public function createProfileWithBankAccount(User $owner, BankAccountRequisites $requisites)
    {
        return $this->createProfileWithPaymentProfile($owner, $this->createBankAccountProfile($owner, $requisites));
    }

	/**
	 * @param ProfileReference $reference
	 * @param CreditCardRequisites $requisites
     * @return null|string
	 */
	public function replaceCreditCard(ProfileReference $reference, CreditCardRequisites $requisites)
	{
		return $this->replaceWithRequisites($reference, $requisites);
	}

    /**
     * @param ProfileReference $reference
     * @param BankAccountRequisites $requisites
     * @return null|string
     */
    public function replaceBankAccount(ProfileReference $reference, BankAccountRequisites $requisites)
    {
       return $this->replaceWithRequisites($reference, $requisites);
    }

    /**
     * @param User $owner
     * @param CustomerPaymentProfileType $paymentProfile
     * @return ReferencesTuple
     */
    private function createProfileWithPaymentProfile(User $owner, CustomerPaymentProfileType $paymentProfile)
    {
        $request = new CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->createAuthentication());
        $request->setRefId($owner->getId());

        $profile = $this->createProfile($owner);
        $profile->addToPaymentProfiles($paymentProfile);

        $request->setProfile($profile);

        $request->setValidationMode(array_take($this->config, 'validation'));

        $controller = new CreateCustomerProfileController($request);

        /**
         * @var CreateCustomerProfileResponse $response
         */
        $response = $controller->executeWithApiResponse($this->config['environment']);

        $this->throwExceptionIfNeeded($response);

        return new ReferencesTuple($response->getCustomerProfileId(), $response->getCustomerPaymentProfileIdList()[0]);
    }

    /**
     * @param ProfileReference $reference
     * @param AbstractRequisites $requisites
     * @return null|string
     */
    private function replaceWithRequisites(ProfileReference $reference, AbstractRequisites $requisites)
    {
        if ($requisites instanceof CreditCardRequisites){
            $paymentProfile = $this->createCreditCardProfile(
                $reference->getOwner(),
                $requisites,
                $reference->getCreditCardProfileId()
            );
        } elseif ($requisites instanceof BankAccountRequisites){
            $paymentProfile = $this->createBankAccountProfile(
                $reference->getOwner(),
                $requisites,
                $reference->getBankAccountProfileId()
            );
        } else {
            throw new RuntimeException('The "'.get_class($requisites).'" is not supported.');
        }

        if ($paymentProfile instanceof CustomerPaymentProfileExType){
            $request = new UpdateCustomerPaymentProfileRequest();
        } else {
            $request = new CreateCustomerPaymentProfileRequest();
        }

        $request->setMerchantAuthentication($this->createAuthentication());
        $request->setRefId($reference->getOwner()->getId());
        $request->setCustomerProfileId($reference->getProfileId());
        $request->setValidationMode(array_take($this->config, 'validation'));

        $request->setPaymentProfile($paymentProfile);

        if ($paymentProfile instanceof CustomerPaymentProfileExType){
            $controller = new UpdateCustomerPaymentProfileController($request);
        } else {
            $controller = new CreateCustomerPaymentProfileController($request);
        }

        $response = $controller->executeWithApiResponse($this->config['environment']);

        $this->throwExceptionIfNeeded($response);

        if ($response instanceof CreateCustomerPaymentProfileResponse){
            return $response->getCustomerPaymentProfileId();
        }

        return null;
    }

	/**
	 * @param ANetApiResponseType $response
	 */
	private function throwExceptionIfNeeded(ANetApiResponseType $response)
	{
		if (strtoupper($response->getMessages()->getResultCode()) === 'OK'){
			return ;
		}

		$error = $response->getMessages()->getMessage()[0];

		throw new PresentableException($error->getText());
	}

	/**
	 * @param User $owner
	 * @return CustomerProfileType
	 */
	private function createProfile(User $owner)
	{
		$profile = new CustomerProfileType();

        $this->defineProfileDetails($profile, $owner);

		return $profile;
	}

    /**
     * @param CustomerProfileBaseType $profile
     * @param User $owner
     */
	private function defineProfileDetails(CustomerProfileBaseType $profile, User $owner)
    {
        $profile->setMerchantCustomerId('VP-'.$owner->getId());

        if ($owner instanceof EmailHolderInterface){
            $profile->setEmail($owner->getEmail());
        }
    }

	/**
     * @param User $owner
	 * @param CreditCardRequisites $requisites
	 * @param int $id
	 * @return CustomerPaymentProfileType|CustomerPaymentProfileExType
	 */
	private function createCreditCardProfile(User $owner, CreditCardRequisites $requisites, $id = null)
	{
        $profile = $this->createRawPaymentProfile($owner, $id);

        $this->defineBillingLocation($profile->getBillTo(), $requisites);

		$acc = new CreditCardType();
		$acc->setCardNumber($requisites->getNumber());

		$month = str_pad($requisites->getExpiresAt()->getMonth(), 2, '0', STR_PAD_LEFT);
		$year = $requisites->getExpiresAt()->getYear();

		$acc->setExpirationDate($year.'-'.$month);
		$acc->setCardCode($requisites->getCode());

		$payment = new PaymentType();
		$payment->setCreditCard($acc);

		$profile->setPayment($payment);

		return $profile;
	}

    /**
     * @param User $owner
     * @param BankAccountRequisites $requisites
     * @param null $id
     * @return CustomerPaymentProfileType
     */
	private function createBankAccountProfile(User $owner, BankAccountRequisites $requisites, $id = null)
    {
        $profile = $this->createRawPaymentProfile($owner, $id);

        $this->defineBillingLocation($profile->getBillTo(), $requisites);

        $account = new BankAccountType();

        $account->setBankName($requisites->getBankName());
        $account->setNameOnAccount($requisites->getNameOnAccount());
        $account->setAccountType((string) $requisites->getAccountType());
        $account->setRoutingNumber($requisites->getRoutingNumber());
        $account->setAccountNumber($requisites->getAccountNumber());

        $payment = new PaymentType();
        $payment->setBankAccount($account);

        $profile->setPayment($payment);

        return $profile;
    }

    private function defineBillingLocation(CustomerAddressType $billing, AbstractRequisites $requisites)
    {
        $billing->setAddress($requisites->getAddress());
        $billing->setCity($requisites->getCity());
        $billing->setZip($requisites->getZip());
        $billing->setState($requisites->getState());
    }

    /**
     * @param User $owner
     * @param null $id
     * @return CustomerPaymentProfileType
     */
    private function createRawPaymentProfile(User $owner, $id = null)
    {
        if ($id){
            $profile = new CustomerPaymentProfileExType();
            $profile->setCustomerPaymentProfileId($id);
        } else {
            $profile = new CustomerPaymentProfileType();
        }

        $this->definePaymentProfileDetails($profile, $owner);

        return $profile;
    }

    /**
     * @param CustomerPaymentProfileType $profile
     * @param User $owner
     */
    private function definePaymentProfileDetails(CustomerPaymentProfileType $profile, User $owner)
    {
        $billing = new CustomerAddressType();

        if ($owner instanceof Amc){
            $profile->setCustomerType('business');
        } else {
            $profile->setCustomerType('individual');
        }

        if ($owner instanceof EmailHolderInterface){
            $billing->setEmail($owner->getEmail());
        }

        if ($owner instanceof IndividualInterface){
            $billing->setFirstName($owner->getFirstName());
            $billing->setLastName($owner->getLastName());
        }

        if ($owner instanceof PhoneHolderInterface){
            $billing->setPhoneNumber($owner->getPhone());
        }

        if ($owner instanceof FaxHolderInterface){
            $billing->setFaxNumber($owner->getFax());
        }

        if ($owner instanceof BusinessInterface){
            $billing->setCompany($owner->getCompanyName());
        }

        $profile->setBillTo($billing);
    }

	/**
	 * @return MerchantAuthenticationType
	 */
	private function createAuthentication()
	{
		$merchantAuthentication = new MerchantAuthenticationType();
		$merchantAuthentication->setName($this->config['login_id']);
		$merchantAuthentication->setTransactionKey($this->config['transaction_key']);

		return $merchantAuthentication;
	}

	/**
	 * @param ProfileReference $reference
	 * @param Purchase $purchase
     * @param Means $means
	 * @return Charge
	 */
	public function charge(ProfileReference $reference, Purchase $purchase, Means $means)
	{
		$paymentProfile = new PaymentProfileType();

        if ($means->is(Means::CREDIT_CARD)){
            $paymentProfile->setPaymentProfileId($reference->getCreditCardProfileId());
        } elseif ($means->is(Means::BANK_ACCOUNT)){
            $paymentProfile->setPaymentProfileId($reference->getBankAccountProfileId());
        } else {
            throw new RuntimeException('Unable to determine the provided payment means.');
        }

		$profileToCharge = new CustomerProfilePaymentType();
		$profileToCharge->setCustomerProfileId($reference->getProfileId());
		$profileToCharge->setPaymentProfile($paymentProfile);

		$transactionRequest = new TransactionRequestType();
		$transactionRequest->setTransactionType('authCaptureTransaction');
		$transactionRequest->setAmount($purchase->getPrice());
		$transactionRequest->setProfile($profileToCharge);

        $product = $purchase->getProduct();

        if ($product instanceof Order){
            $order = new OrderType();
            $order->setInvoiceNumber($product->getId());
            $order->setDescription(sprintf('Customer: %s; File#: %s; Loan#: %s;',
                $product->getCustomer()->getDisplayName(),
                $product->getFileNumber(),
                $product->getLoanNumber() ?? ''));
            $transactionRequest->setOrder($order);
        }

        if ($product instanceof Invoice){
            $order = new OrderType();
            $order->setInvoiceNumber($product->getId());
            $transactionRequest->setOrder($order);

            $lines = [];

            foreach ($product->getItems() as $item){
                $line = new LineItemType();

                $line->setItemId($item->getId());
                $line->setQuantity(1);
                $line->setName('File #: '.$item->getFileNumber());
                $line->setUnitPrice($item->getAmount());
                $line->setDescription('Address: '.$item->getAddress().'; Borrower Name: '.$item->getBorrowerName().';');

                $lines[] = $line;
            }

            $transactionRequest->setLineItems($lines);
        }


		$request = new CreateTransactionRequest();
		$request->setMerchantAuthentication($this->createAuthentication());
		$request->setRefId($reference->getOwner()->getId());
		$request->setTransactionRequest($transactionRequest);

		$controller = new CreateTransactionController($request);

		/**
		 * @var CreateTransactionResponse $response
		 */
		$response = $controller->executeWithApiResponse($this->config['environment']);

        $charge = new Charge();

        if (strtoupper($response->getMessages()->getResultCode()) === 'OK'){
            $transaction = $response->getTransactionResponse();

            $status = $this->createChargeStatus($transaction);

            $charge->setStatus($status);
            $charge->setMessage($this->prepareChargeMessage($transaction));

            $charge->setTransactionId($transaction->getTransId());
        } else {
            $charge->setStatus(new Status(Status::ERROR));

            $error = $response->getMessages()->getMessage()[0];

            $charge->setMessage($error->getText());
        }

		return $charge;
	}

	/**
	 * @param TransactionResponseType $transaction
	 * @return string
	 */
	private function prepareChargeMessage(TransactionResponseType $transaction)
	{
		$code = $transaction->getResponseCode();

		if (in_array($code, [2, 4])){
			$message = $transaction->getMessages()[0] ?? new MessageAType();
			return $message->getDescription();
		}

		if ($code == 3){
			$error = $transaction->getErrors()[0];
			return $error->getErrorText();
		}

		return null;
	}

	/**
	 * @param TransactionResponseType $response
	 * @return Status
	 */
	private function createChargeStatus(TransactionResponseType $response)
	{
		if ($response->getResponseCode() == 1){
			return new Status(Status::APPROVED);
		}

		if ($response->getResponseCode() == 2){
			return new Status(Status::DECLINED);
		}

		if ($response->getResponseCode() == 3){
			return new Status(Status::ERROR);
		}

		if ($response->getResponseCode() == 4){
			return new Status(Status::PENDING);
		}

		throw new RuntimeException('Unknown response code: "'.$response->getResponseCode().'".');
	}

    /**
     * @param ProfileReference $reference
     * @param User $owner
     */
    public function refreshProfile(ProfileReference $reference, User $owner)
    {
        $profile = new CustomerProfileExType();
        $profile->setCustomerProfileId($reference->getProfileId());

        $this->defineProfileDetails($profile, $owner);

        $request = new UpdateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->createAuthentication());
        $request->setProfile($profile);


        $controller = new UpdateCustomerProfileController($request);

        /**
         * @var UpdateCustomerProfileResponse $response
         */
        $response = $controller->executeWithApiResponse($this->config['environment']);

        $this->throwExceptionIfNeeded($response);

        if ($reference->getBankAccountProfileId()){
            $this->refreshPaymentProfile($reference, Means::BANK_ACCOUNT, $owner);
        }

        if ($reference->getCreditCardProfileId()){
            $this->refreshPaymentProfile($reference, Means::CREDIT_CARD, $owner);
        }
    }

    /**
     * @param ProfileReference $reference
     * @param $means
     * @param User $owner
     */
    private function refreshPaymentProfile(ProfileReference $reference, $means, User $owner)
    {
        $paymentProfile = new CustomerPaymentProfileExType();

        if ($means == Means::BANK_ACCOUNT){
            $paymentProfile->setCustomerPaymentProfileId($reference->getBankAccountProfileId());

            $ba = new BankAccountType();

            $ba->setRoutingNumber('XXXX'.$reference->getMaskedRoutingNumber());
            $ba->setAccountNumber('XXXX'.$reference->getMaskedAccountNumber());
            $ba->setAccountType((string) $reference->getAccountType());
            $ba->setBankName($reference->getBankName());
            $ba->setNameOnAccount($reference->getNameOnAccount());

            $paymentProfile->setPayment((new PaymentType())->setBankAccount($ba));

        } else {
            $paymentProfile->setCustomerPaymentProfileId($reference->getCreditCardProfileId());

            $cc = new CreditCardType();
            $cc->setExpirationDate('XXXX');
            $cc->setCardNumber('XXXX'.$reference->getMaskedCreditCardNumber());

            $paymentProfile->setPayment((new PaymentType())->setCreditCard($cc));
        }


        $this->definePaymentProfileDetails($paymentProfile, $owner);

        $request = new UpdateCustomerPaymentProfileRequest();
        $request->setCustomerProfileId($reference->getProfileId());
        $request->setMerchantAuthentication($this->createAuthentication());
        $request->setPaymentProfile($paymentProfile);

        $controller = new UpdateCustomerPaymentProfileController($request);

        /**
         * @var UpdateCustomerPaymentProfileResponse $response
         */
        $response = $controller->executeWithApiResponse($this->config['environment']);

        $this->throwExceptionIfNeeded($response);
    }
}
