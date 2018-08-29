<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\InvitationsProcessor;
use ValuePad\Api\Invitation\V2_0\Processors\InvitationsSearchableProcessor;
use ValuePad\Api\Invitation\V2_0\Transformers\InvitationTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Invitation\Options\FetchInvitationsOptions;
use ValuePad\Core\Invitation\Services\InvitationService;
use ValuePad\Core\Shared\Options\PaginationOptions;

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
	 * @param int $customerId
	 * @param InvitationsProcessor $processor
	 * @return Response
	 * @throws  ErrorsThrowableCollection
	 */
	public function store($customerId, InvitationsProcessor $processor)
	{
		return $this->resource->make(
			$this->invitationService->create($customerId, $processor->createPersistable()),
			$this->transformer(InvitationTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param InvitationsSearchableProcessor $processor
	 * @return Response
	 */
	public function index($customerId, InvitationsSearchableProcessor $processor)
	{
		$adapter = new DefaultPaginatorAdapter([
			'getAll' => function($page, $perPage) use ($customerId, $processor){
				$options = new FetchInvitationsOptions();
				$options->setPagination(new PaginationOptions($page, $perPage));
				$options->setSortables($processor->createSortables());
				$options->setCriteria($processor->createCriteria());
				return $this->invitationService->getAllByCustomerId($customerId, $options);
			},
			'getTotal' => function() use ($customerId, $processor){
				return $this->invitationService->getTotalByCustomerId($customerId);
			}
		]);

		return $this->resource->makeAll(
			$this->paginator($adapter),
			$this->transformer(InvitationTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $invitationId
	 * @return Response
	 */
	public function show($customerId, $invitationId)
	{
		return $this->resource->make(
			$this->invitationService->get($invitationId),
			$this->transformer(InvitationTransformer::class)
		);
	}

	/**
	 * @param CustomerService $customerService
	 * @param int $customerId
	 * @param int $invitationId
	 * @return bool
	 */
	public static function verifyAction(CustomerService $customerService, $customerId, $invitationId = null)
	{
		if (!$customerService->exists($customerId)){
			return false;
		}

		if ($invitationId === null){
			return true;
		}

		return $customerService->hasInvitation($customerId, $invitationId);
	}
}
