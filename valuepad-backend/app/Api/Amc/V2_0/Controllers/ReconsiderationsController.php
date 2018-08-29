<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Ascope\Libraries\Verifier\Action;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Transformers\ReconsiderationTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraisal\Services\ReconsiderationService;

class ReconsiderationsController extends BaseController
{
    /**
     * @var ReconsiderationService
     */
    private $reconsiderationService;

    /**
     * @param ReconsiderationService $reconsiderationService
     */
    public function initialize(ReconsiderationService $reconsiderationService)
    {
        $this->reconsiderationService = $reconsiderationService;
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function index($amcId, $orderId)
    {
        return $this->resource->makeAll(
            $this->reconsiderationService->getAll($orderId),
            $this->transformer(ReconsiderationTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $reconsiderationId
     * @return Response
     */
    public function show($amcId, $reconsiderationId)
    {
        return $this->resource->make(
            $this->reconsiderationService->get($reconsiderationId),
            $this->transformer(ReconsiderationTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @param int $reconsiderationId
     * @return Response
     */
    public function showByOrder($amcId, $orderId, $reconsiderationId)
    {
        return $this->show($amcId, $reconsiderationId);
    }

    /**
     * @param Action $action
     * @param AmcService $amcService
     * @param int $amcId
     * @param int $reconsiderationIdOrOrderId
     * @param int $reconsiderationId
     * @return bool
     */
    public static function verifyAction(Action $action, AmcService $amcService, $amcId, $reconsiderationIdOrOrderId, $reconsiderationId = null)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        if ($action->is(['index', 'showByOrder'])){
            return $amcService->hasOrder($amcId, $reconsiderationIdOrOrderId);
        }

        if ($reconsiderationId !== null){
            $reconsiderationIdOrOrderId = $reconsiderationId;
        }

        return $amcService->hasReconsideration($amcId, $reconsiderationIdOrOrderId);
    }
}
