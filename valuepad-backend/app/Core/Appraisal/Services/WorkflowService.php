<?php
namespace ValuePad\Core\Appraisal\Services;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Options\InspectionOptions;
use ValuePad\Core\Support\Service\AbstractService;
use DateTime;

class WorkflowService extends AbstractService
{
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var InspectionService
     */
    private $inspectionService;

    /**
     * @var RevisionService
     */
    private $revisionService;

    /**
     * @param OrderService $orderService
     * @param InspectionService $inspectionService
     * @param RevisionService $revisionService
     */
    public function initialize(
        OrderService $orderService,
        InspectionService $inspectionService,
        RevisionService $revisionService
    )
    {
        $this->orderService = $orderService;
        $this->inspectionService = $inspectionService;
        $this->revisionService = $revisionService;
    }

    /**
     * @param int $id
     */
    public function fresh($id)
    {
        $this->orderService->updateProcessStatus($id, new ProcessStatus(ProcessStatus::FRESH));
    }

    /**
     * @param int $id
     */
    public function requestForBid($id)
    {
        $this->orderService->updateProcessStatus($id, new ProcessStatus(ProcessStatus::REQUEST_FOR_BID));
    }

    /**
     * @param int $id
     */
    public function accepted($id)
    {
        $this->orderService->accept($id);
    }

    /**
     * @param int $id
     * @param DateTime $scheduledAt
     * @param DateTime $estimatedCompletionDate
     * @param InspectionOptions $options
     */
    public function inspectionScheduled($id, DateTime $scheduledAt, DateTime $estimatedCompletionDate, InspectionOptions $options = null)
    {
        $this->inspectionService->schedule($id, $scheduledAt, $estimatedCompletionDate, $options);
    }

    /**
     * @param int $id
     * @param DateTime $completedAt
     * @param DateTime $estimatedCompletionDate
     * @param InspectionOptions $options
     */
    public function inspectionCompleted($id, DateTime $completedAt, DateTime $estimatedCompletionDate, InspectionOptions $options = null)
    {
        $this->inspectionService->complete($id, $completedAt, $estimatedCompletionDate, $options);
    }

    /**
     * @param int $id
     */
    public function readyForReview($id)
    {
        $this->orderService->updateProcessStatus($id, new ProcessStatus(ProcessStatus::READY_FOR_REVIEW));
    }

    /**
     * @param int $id
     */
    public function late($id)
    {
        $this->orderService->updateProcessStatus($id, new ProcessStatus(ProcessStatus::LATE));
    }

    /**
     * @param int $id
     * @param string $explanation
     */
    public function onHold($id, $explanation)
    {
        $this->orderService->postpone($id, $explanation);
    }

    /**
     * @param int $id
     */
    public function revisionPending($id)
    {
        $this->orderService->updateProcessStatus($id, new ProcessStatus(ProcessStatus::REVISION_PENDING));
    }

    /**
     * @param int $id
     */
    public function revisionInReview($id)
    {
        $this->orderService->updateProcessStatus($id, new ProcessStatus(ProcessStatus::REVISION_IN_REVIEW));
    }

    /**
     * @param int $id
     */
    public function reviewed($id)
    {
        $this->orderService->updateProcessStatus($id, new ProcessStatus(ProcessStatus::REVIEWED));
    }

    /**
     * @param int $id
     */
    public function completed($id)
    {
        $this->orderService->updateProcessStatus($id, new ProcessStatus(ProcessStatus::COMPLETED));
    }

    /**
     * @param int $id
     */
    public function resume($id)
    {
        $order = $this->orderService->get($id);

        $workflow = $order->getWorkflow();
        $status = array_last($workflow->toArray(), function ($i, $status) {
            return $status !== ProcessStatus::ON_HOLD;
        });

        if (!$status) {
            return;
        }

        $status = new ProcessStatus($status);

        if ($status->is(ProcessStatus::FRESH)) {
            return $this->fresh($id);
        }

        if ($status->is(ProcessStatus::INSPECTION_SCHEDULED)) {
            return $this->inspectionScheduled(
                $id, $order->getInspectionScheduledAt(), $order->getEstimatedCompletionDate()
            );
        }

        if ($status->is(ProcessStatus::INSPECTION_COMPLETED)) {
            return $this->inspectionCompleted(
                $id, $order->getInspectionCompletedAt(), $order->getEstimatedCompletionDate()
            );
        }

        return $this->{camel_case($status->value())}($id);
    }
}
