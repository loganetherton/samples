<?php
namespace ValuePad\Api\Company\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\CompleteInspectionProcessor;
use ValuePad\Api\Appraisal\V2_0\Processors\ScheduleInspectionProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Services\InspectionService;
use ValuePad\Core\Company\Services\ManagerService;

class InspectionController extends BaseController
{
    /**
     * @var InspectionService
     */
    private $inspectionService;

    /**
     * @param InspectionService $inspectionService
     */
    public function initialize(InspectionService $inspectionService)
    {
        $this->inspectionService = $inspectionService;
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @param ScheduleInspectionProcessor $processor
     * @return Response
     */
    public function schedule($managerId, $orderId, ScheduleInspectionProcessor $processor)
    {
        $this->inspectionService->schedule(
            $orderId,
            $processor->getScheduledAt(),
            $processor->getEstimatedCompletionDate()
        );

        return $this->resource->blank();
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @param CompleteInspectionProcessor $processor
     * @return Response
     */
    public function complete($managerId, $orderId, CompleteInspectionProcessor $processor)
    {
        $this->inspectionService->complete(
            $orderId,
            $processor->getCompletedAt(),
            $processor->getEstimatedCompletionDate()
        );

        return $this->resource->blank();
    }

    /**
     * @param ManagerService $managerService
     * @param int $managerId
     * @param int $orderId
     * @return bool
     */
    public static function verifyAction(ManagerService $managerService, $managerId, $orderId)
    {
        if (!$managerService->exists($managerId)){
            return false;
        }

        return $managerService->hasOrder($managerId, $orderId);
    }
}
