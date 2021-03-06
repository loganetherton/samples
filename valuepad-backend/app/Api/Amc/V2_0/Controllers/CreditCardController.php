<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Assignee\V2_0\Processors\CreditCardProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Payment\Objects\CreditCard;
use ValuePad\Core\Payment\Services\PaymentService;

class CreditCardController extends BaseController
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
     * @param CreditCardProcessor $processor
     * @return Response
     */
    public function replace($amcId, CreditCardProcessor $processor)
    {
        return $this->resource->make(
            $this->paymentService->switchCreditCard($amcId, $processor->createRequisites()),
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
            $this->paymentService->getCreditCard($amcId) ?? new CreditCard(),
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
