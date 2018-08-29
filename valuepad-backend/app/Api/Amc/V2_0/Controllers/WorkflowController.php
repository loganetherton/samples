<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\CompleteInspectionProcessor;
use ValuePad\Api\Appraisal\V2_0\Processors\ScheduleInspectionProcessor;
use ValuePad\Api\Amc\V2_0\Processors\PostponeProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Notifications\SendMessageNotification;
use ValuePad\Core\Appraisal\Services\WorkflowService;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Letter\Support\Notifier;

class WorkflowController extends BaseController
{
    /**
     * @var WorkflowService
     */
    private $workflowService;

    /**
     * @param WorkflowService $workflowService
     */
    public function initialize(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function fresh($amcId, $orderId)
    {
        $this->workflowService->fresh($orderId);

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function requestForBid($amcId, $orderId)
    {
        $this->workflowService->requestForBid($orderId);

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function accepted($amcId, $orderId)
    {
        $this->workflowService->accepted($orderId);

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @param ScheduleInspectionProcessor $processor
     * @return Response
     */
    public function inspectionScheduled($amcId, $orderId, ScheduleInspectionProcessor $processor)
    {
        $this->workflowService->inspectionScheduled(
            $orderId,
            $processor->getScheduledAt(),
            $processor->getEstimatedCompletionDate()
        );

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @param CompleteInspectionProcessor $processor
     * @return Response
     */
    public function inspectionCompleted($amcId, $orderId, CompleteInspectionProcessor $processor)
    {
        $this->workflowService->inspectionCompleted(
            $orderId,
            $processor->getCompletedAt(),
            $processor->getEstimatedCompletionDate()
        );

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function readyForReview($amcId, $orderId)
    {
        $this->workflowService->readyForReview($orderId);
        
        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function late($amcId, $orderId)
    {
        $this->workflowService->late($orderId);

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @param PostponeProcessor $processor
     * @return Response
     */
    public function onHold($amcId, $orderId, PostponeProcessor $processor)
    {
        $this->container->resolving(function(Notifier $notifier) use ($processor) {
            $notifier->addFilter(function($notification) use ($processor){
                if ($notification instanceof SendMessageNotification){
                    return null;
                }

                return $notification;
            });
        });

        $this->workflowService->onHold($orderId, $processor->getExplanation());

        return $this->resource->blank();
    }


    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function revisionPending($amcId, $orderId)
    {
        $this->workflowService->revisionPending($orderId);
        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function revisionInReview($amcId, $orderId)
    {
        $this->workflowService->revisionInReview($orderId);

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function reviewed($amcId, $orderId)
    {
        $this->workflowService->reviewed($orderId);

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function completed($amcId, $orderId)
    {
        $this->workflowService->completed($orderId);

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function resume($amcId, $orderId)
    {
        $this->workflowService->resume($orderId);

        return $this->resource->blank();
    }

    /**
     * @param AmcService $amcService
     * @param int $amcId
     * @param int $orderId
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, $amcId, $orderId)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        return $amcService->hasOrder($amcId, $orderId);
    }
}
