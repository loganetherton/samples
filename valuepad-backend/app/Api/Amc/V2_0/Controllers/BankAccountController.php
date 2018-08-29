<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Amc\V2_0\Processors\BankAccountProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Payment\Objects\BankAccount;
use ValuePad\Core\Payment\Services\PaymentService;

class BankAccountController extends BaseController
{
    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @param PaymentService $paymentService
     */
    public function initialize(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @param int $amcId
     * @param BankAccountProcessor $processor
     * @return Response
     */
    public function change($amcId, BankAccountProcessor $processor)
    {
        return $this->resource->make(
            $this->paymentService->changeBankAccount($amcId, $processor->createRequisites()),
            $this->transformer()
        );
    }

    /**
     * @param int $amcId
     * @return Response
     */
    public function show($amcId)
    {
        return $this->resource->make(
            $this->paymentService->getBankAccount($amcId) ?? new BankAccount(),
            $this->transformer()
        );
    }

    /**
     * @param AmcService $amcService
     * @param int $amcId
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, $amcId)
    {
        return $amcService->exists($amcId);
    }
}
