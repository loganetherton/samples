<?php
namespace ValuePad\DAL\Customer\Support;
use Ascope\Libraries\Validation\PresentableException;
use GuzzleHttp\Exception\BadResponseException;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Exceptions\WalletTransactionException;
use ValuePad\Core\Customer\Interfaces\WalletInterface;
use ValuePad\Core\Customer\Objects\PayoffCreditCardRequisites;
use ValuePad\Core\Customer\Objects\PayoffPurchase;
use ValuePad\Push\Support\MaskedCardNumberListener;
use ValuePad\Push\Support\Tunnel;

class Wallet implements WalletInterface
{
    /**
     * @var MaskedCardNumberListener
     */
    private $listener;

    /**
     * @param MaskedCardNumberListener $listener
     */
    public function __construct(MaskedCardNumberListener $listener)
    {
        $this->listener = $listener;
    }

    /**
     * @param PayoffCreditCardRequisites $requisites
     * @param PayoffPurchase $purchase
     */
    public function pay(PayoffCreditCardRequisites $requisites, PayoffPurchase $purchase)
    {
        /**
         * @var Order $order
         */
        $order = $purchase->getProduct();

        $amc = $order->getAssignee();

        if (!$amc instanceof Amc){
            throw new PresentableException('Unable to pay to non-AMC assignee');
        }

        $settings = $amc->getSettings();

        if (!$settings->getPushUrl()){
            throw new PresentableException('This AMC does not expect to process the payment on its end');
        }

        $tunnel = new Tunnel($settings->getPushUrl(), $amc->getSecret1(), $amc->getSecret2());
        $tunnel->setListener($this->listener);

        try {
            $tunnel->push('order', 'payoff', [
                'order' => $order->getId(),
                'amount' => $purchase->getPrice(),
                'creditCard' => [
                    'number' => $requisites->getNumber(),
                    'code' => $requisites->getCode(),
                    'expiresAt' => [
                        'month' => $requisites->getExpiresAt()->getMonth(),
                        'year' => $requisites->getExpiresAt()->getYear()
                    ],
                    'firstName' => $requisites->getFirstName(),
                    'lastName' => $requisites->getLastName(),
                    'address' => $requisites->getAddress(),
                    'city' => $requisites->getCity(),
                    'state' => $requisites->getState(),
                    'zip' => $requisites->getZip(),
                    'email' => $requisites->getEmail(),
                    'phone' => $requisites->getPhone()
                ]
            ]);
        } catch (BadResponseException $exception){
            $data = (string) $exception->getResponse()->getBody();
            $data = json_decode($data, true);

            if ($data === null){
                throw $exception;
            }

            if (!isset($data['message'])){
                throw $exception;
            }

            $message = $data['message'];

            $code = null;

            if (isset($data['code'])) {
                $code = $data['code'];
            }

            throw new WalletTransactionException($message, $code);
        }
    }
}
