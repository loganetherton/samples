<?php
namespace ValuePad\Api\Company\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Company\V2_0\Processors\PermissionsProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Services\PermissionService;

class PermissionsController extends BaseController
{
    /**
     * @var PermissionService
     */
    private $permissionService;

    /**
     * @param PermissionService $permissionService
     */
    public function initialize(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * @param int $companyId
     * @param int $staffId
     * @return Response
     */
    public function index($companyId, $staffId)
    {
        return $this->resource->makeAll(
            $this->permissionService->getAllAppraiserStaff($staffId),
            $this->transformer()
        );
    }

    /**
     * @param int $companyId
     * @param int $staffId
     * @param PermissionsProcessor $processor
     * @return Response
     */
    public function replace($companyId, $staffId, PermissionsProcessor $processor)
    {
        $this->permissionService->replaceAllAppraiserStaff($staffId, $processor->getAppraiserStaffIds());

        return $this->resource->blank();
    }

    /**
     * @param CompanyService $companyService
     * @param int $companyId
     * @param int $staffId
     * @return bool
     */
    public static function verifyAction(CompanyService $companyService, $companyId, $staffId)
    {
        if (!$companyService->exists($companyId)){
            return false;
        }

        return $companyService->hasStaff($companyId, $staffId);
    }
}
