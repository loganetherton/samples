<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Invitation\V2_0\Processors\InvitationsSearchableProcessor;
use ValuePad\Api\Invitation\V2_0\Transformers\InvitationTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Invitation\Options\FetchInvitationsOptions;
use ValuePad\Core\Invitation\Services\InvitationService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class InvitationsController extends BaseController
{
	/**
	 * @var InvitationService $invitationService
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
	 * @param InvitationsSearchableProcessor $processor
	 * @return Response
	 */
	public function index($appraiserId, InvitationsSearchableProcessor $processor)
	{
		$adapter = new DefaultPaginatorAdapter([
			'getAll' => function($page, $perPage) use ($appraiserId, $processor){
				$options = new FetchInvitationsOptions();
				$options->setPagination(new PaginationOptions($page, $perPage));
				$options->setSortables($processor->createSortables());
				$options->setCriteria($processor->getCriteria());
				return $this->invitationService->getAllByAppraiserId($appraiserId, $options);
			},
			'getTotal' => function() use ($appraiserId, $processor){
				return $this->invitationService->getTotalByAppraiserId($appraiserId, $processor->getCriteria());
			}
		]);

		return $this->resource->makeAll(
			$this->paginator($adapter),
			$this->transformer(InvitationTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @param int $invitationId
	 * @return Response
	 */
	public function show($appraiserId, $invitationId)
	{
		return $this->resource->make(
			$this->invitationService->get($invitationId),
			$this->transformer(InvitationTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @param int $invitationId
	 * @return Response
	 */
	public function accept($appraiserId, $invitationId)
	{
		$this->invitationService->accept($invitationId, $appraiserId);

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
	public static function verifyAction(AppraiserService $appraiserService, $appraiserId, $invitationId = null)
	{
		if (!$appraiserService->exists($appraiserId)){
			return false;
		}

		if ($invitationId === null){
			return true;
		}

		return $appraiserService->hasInvitation($appraiserId, $invitationId);
	}
}
