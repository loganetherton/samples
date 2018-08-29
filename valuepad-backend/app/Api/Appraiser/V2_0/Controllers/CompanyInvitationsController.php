<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Company\Services\InvitationService;

class CompanyInvitationsController extends BaseController
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
     * @param int $appraiserId
     * @return Response
     */
    public function index($appraiserId)
    {
        return $this->resource->makeAll(
            $this->invitationService->getAllByAppraiserId($appraiserId),
            $this->transformer()
        );
    }

    /**
     * @param int $appraiserId
     * @param int $invitationId
     * @return Response
     */
    public function accept($appraiserId, $invitationId)
    {
        $this->invitationService->accept($invitationId);

        return $this->resource->blank();
    }

    /**
     * @param int $appraiserId
     * @param int $invitationId
     * @return Response
     */
    public function decline($appraiserId, $invitationId)
    {
        $this->invitationService->decline($invitationId);

        return $this->resource->blank();
    }

    /**
     * @param AppraiserService $appraiserService
     * @param int $appraiserId
     * @param int $invitationId
     * @return bool
     */
    public static function verifyAction(
        AppraiserService $appraiserService,
        $appraiserId,
        $invitationId = null
    ) {
        if (! $appraiserService->exists($appraiserId)) {
            return false;
        }

        if ($invitationId && ! $appraiserService->hasCompanyInvitation($appraiserId, $invitationId)) {
            return false;
        }

        return true;
    }
}
