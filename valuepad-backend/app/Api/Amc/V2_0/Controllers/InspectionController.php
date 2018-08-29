<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\CompleteInspectionProcessor;
use ValuePad\Api\Appraisal\V2_0\Processors\ScheduleInspectionProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraisal\Services\InspectionService;

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
     * @param int $amcId
     * @param int $orderId
     * @param ScheduleInspectionProcessor $processor
     * @return Response
     */
    public function schedule($amcId, $orderId, ScheduleInspectionProcessor $processor)
    {
        $this->inspectionService->schedule(
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
    public function complete($amcId, $orderId, CompleteInspectionProcessor $processor)
    {
        $this->inspectionService->complete(
            $orderId,
            $processor->getCompletedAt(),
            $processor->getEstimatedCompletionDate()
        );

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
