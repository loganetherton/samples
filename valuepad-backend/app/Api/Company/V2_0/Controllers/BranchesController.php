<?php
namespace ValuePad\Api\Company\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Company\V2_0\Processors\BranchesProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Company\Services\BranchService;
use ValuePad\Core\Company\Services\CompanyService;

class BranchesController extends BaseController
{
    /**
     * @var BranchService
     */
    private $branchService;

    /**
     * @param BranchService $branchService
     */
    public function initialize(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    /**
     * @param int $companyId
     * @return Response
     */
    public function index($companyId)
    {
        $branches = $this->branchService->getAll($companyId);

        return $this->resource->make($branches, $this->transformer());
    }

    /**
     * @param int $companyId
     * @param BranchesProcessor $processor
     * @return Response
     */
    public function store($companyId, BranchesProcessor $processor)
    {
        $branch = $this->branchService->create($companyId, $processor->createPersistable());

        return $this->resource->make($branch, $this->transformer());
    }

    /**
     * @param int $companyId
     * @param int $branchId
     * @param BranchesProcessor $processor
     * @return Response
     */
    public function update($companyId, $branchId, BranchesProcessor $processor)
    {
        $this->branchService->update($branchId, $processor->createPersistable());

        return $this->resource->blank();
    }

    /**
     * @param int $companyId
     * @param int $branchId
     * @return Response
     */
    public function destroy($companyId, $branchId)
    {
        $this->branchService->delete($branchId);

        return $this->resource->blank();
    }

    /**
     * @param CompanyService $companyService
     * @param int $companyId
     * @param int $branchId
     * @return bool
     */
    public static function verifyAction(CompanyService $companyService, $companyId, $branchId = null)
    {
        if (! $companyService->exists($companyId)) {
            return false;
        }

        if ($branchId && ! $companyService->hasBranch($companyId, $branchId)) {
            return false;
        }

        return true;
    }
}
