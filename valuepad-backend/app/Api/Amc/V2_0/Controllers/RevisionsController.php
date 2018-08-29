<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Ascope\Libraries\Verifier\Action;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Transformers\RevisionTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraisal\Services\RevisionService;

class RevisionsController extends BaseController
{
    /**
     * @var RevisionService
     */
    private $revisionService;

    /**
     * @param RevisionService $revisionService
     */
    public function initialize(RevisionService  $revisionService)
    {
        $this->revisionService = $revisionService;
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function index($amcId, $orderId)
    {
        return $this->resource->makeAll(
            $this->revisionService->getAll($orderId),
            $this->transformer(RevisionTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $revisionId
     * @return Response
     */
    public function show($amcId, $revisionId)
    {
        return $this->resource->make(
            $this->revisionService->get($revisionId),
            $this->transformer(RevisionTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @param int $revisionId
     * @return Response
     */
    public function showByOrder($amcId, $orderId, $revisionId)
    {
        return $this->show($amcId, $revisionId);
    }

    /**
     * @param Action $action
     * @param AmcService $amcService
     * @param int $amcId
     * @param int $revisionIdOrOrderId
     * @param int $revisionId
     * @return bool
     */
    public static function verifyAction(Action $action, AmcService $amcService, $amcId, $revisionIdOrOrderId, $revisionId = null)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        if ($action->is(['index', 'showByOrder'])){
            return $amcService->hasOrder($amcId, $revisionIdOrOrderId);
        }

        if ($revisionId !== null){
            $revisionIdOrOrderId = $revisionId;
        }

        return $amcService->hasRevision($amcId, $revisionIdOrOrderId);
    }
}
