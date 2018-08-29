<?php
namespace ValuePad\Api\Company\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Company\V2_0\Processors\ManagersProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Shared\Processors\AvailabilityProcessor;
use ValuePad\Core\Company\Services\ManagerService;

class ManagersController extends BaseController
{
    /**
     * @var ManagerService
     */
    private $managerService;

    /**
     * @param ManagerService $managerService
     */
    public function initialize(ManagerService $managerService)
    {
        $this->managerService = $managerService;
    }

    /**
     * @param int $managerId
     * @param ManagersProcessor $processor
     * @return Response
     */
    public function update($managerId, ManagersProcessor $processor)
    {
        $this->managerService->update(
            $managerId,
            $processor->createPersistable(),
            $processor->schedulePropertiesToClear()
        );

        return $this->resource->blank();
    }

    /**
     * @param int $managerId
     * @return Response
     */
    public function show($managerId)
    {
        return $this->resource->make($this->managerService->get($managerId), $this->transformer());
    }

    /**
     * @param ManagerService $managerService
     * @param int $managerId
     * @return bool
     */
    public static function verifyAction(ManagerService $managerService, $managerId)
    {
        return $managerService->exists($managerId);
    }

    /**
     * @param int $managerId
     * @param AvailabilityProcessor $processor
     * @return Response
     */
    public function updateAvailability($managerId, AvailabilityProcessor $processor)
    {
        $this->managerService->updateAvailability(
            $managerId,
            $processor->createPersistable(),
            $processor->schedulePropertiesToClear()
        );

        return $this->resource->blank();
    }
}
