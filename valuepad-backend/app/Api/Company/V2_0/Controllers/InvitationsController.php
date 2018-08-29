<?php
namespace ValuePad\Api\Company\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Company\V2_0\Processors\InvitationsProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Services\InvitationService;

class InvitationsController extends BaseController
{
    /**
     * @var InvitationService
     */
    private $invitationService;

    /**
     * @param InvitationService $invitationService
     */
    public function initialize(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    /**
     * @param int $companyId
     * @param int $branchId
     * @return Response
     */
    public function index($companyId, $branchId)
    {
        return $this->resource->makeAll(
            $this->invitationService->getAll($branchId),
            $this->transformer()
        );
    }

    /**
     * @param int $companyId
     * @param int $branchId
     * @param InvitationsProcessor $procesor
     * @return Response
     */
    public function store($companyId, $branchId, InvitationsProcessor $processor)
    {
        return $this->resource->make(
            $this->invitationService->create($branchId, $processor->createPersistable()),
            $this->transformer()
        );
    }

    /**
     * @param CompanyService $companyService
     * @param int $companyId
     * @param int $branchId
     * @return bool
     */
    public static function verifyAction(CompanyService $companyService, $companyId, $branchId)
    {
        return $companyService->exists($companyId) && $companyService->hasBranch($companyId, $branchId);
    }
}
