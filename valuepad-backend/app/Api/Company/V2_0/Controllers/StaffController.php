<?php
namespace ValuePad\Api\Company\V2_0\Controllers;
use Ascope\Libraries\Verifier\Action;
use Illuminate\Http\Response;
use ValuePad\Api\Company\V2_0\Processors\ManagerAsStaffProcessor;
use ValuePad\Api\Company\V2_0\Processors\StaffProcessor;
use ValuePad\Api\Company\V2_0\Processors\StaffSearchableProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Company\Notifications\CreateStaffNotification;
use ValuePad\Core\Company\Options\FetchStaffOptions;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Services\StaffService;
use ValuePad\Letter\Support\Notifier;

class StaffController extends BaseController
{
    /**
     * @var StaffService
     */
    private $staffService;

    /**
     * @param StaffService $staffService
     */
    public function initialize(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    /**
     * @param int $companyId
     * @return Response
     */
    public function index($companyId)
    {
        return $this->resource->makeAll(
            $this->staffService->getAllByCompanyId($companyId),
            $this->transformer()
        );
    }

    /**
     * @param $companyId
     * @param $branchId
     * @param StaffSearchableProcessor $processor
     * @return Response
     */
    public function indexByBranch($companyId, $branchId, StaffSearchableProcessor $processor)
    {
        $options = new FetchStaffOptions();
        $options->setCriteria($processor->createCriteria());

        return $this->resource->makeAll(
            $this->staffService->getAllByBranchId($branchId, $options),
            $this->transformer()
        );
    }

    /**
     * @param int $companyId
     * @param ManagerAsStaffProcessor $processor
     * @return Response
     */
    public function storeManager($companyId, ManagerAsStaffProcessor $processor)
    {
        $this->container->resolving(function(Notifier $notifier) use ($processor) {
            $notifier->addFilter(function($notification) use ($processor){
                if (!$notification instanceof CreateStaffNotification){
                    return $notification;
                }

                return $processor->notifyUser() ? $notification : null;
            });
        });

        $staff = $this->staffService->createManager($companyId, $processor->createPersistable());

        return $this->resource->make($staff, $this->transformer());
    }

    /**
     * @param int $companyId
     * @param int $staffId
     * @param StaffProcessor $processor
     * @return Response
     */
    public function update($companyId, $staffId, StaffProcessor $processor)
    {
        $this->staffService->update($staffId, $processor->createPersistable());

        return $this->resource->blank();
    }

    /**
     * @param int $companyId
     * @param int $staffId
     * @return Response
     */
    public function show($companyId, $staffId)
    {
        return $this->resource->make($this->staffService->get($staffId), $this->transformer());
    }

    /**
     * @param int $companyId
     * @param int $staffId
     * @return Response
     */
    public function destroy($companyId, $staffId)
    {
        $this->staffService->delete($staffId);

        return $this->resource->blank();
    }

    /**
     * @param Action $action
     * @param CompanyService $companyService
     * @param int $companyId
     * @param int $staffIdOrBranchId
     * @return bool
     */
    public static function verifyAction(
        Action $action,
        CompanyService $companyService,
        $companyId,
        $staffIdOrBranchId = null
    )
    {
        if (!$companyService->exists($companyId)){
            return false;
        }

        if ($action->is('indexByBranch')){
            return $companyService->hasBranch($companyId, $staffIdOrBranchId);
        }

        if ($staffIdOrBranchId === null){
            return true;
        }

        return $companyService->hasStaff($companyId, $staffIdOrBranchId);
    }
}
