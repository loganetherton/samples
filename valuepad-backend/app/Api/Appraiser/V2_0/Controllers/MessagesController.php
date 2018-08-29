<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Ascope\Libraries\Verifier\Action;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\MessagesProcessor;
use ValuePad\Api\Appraisal\V2_0\Processors\MessagesSearchableProcessor;
use ValuePad\Api\Appraisal\V2_0\Processors\SelectedMessagesProcessor;
use ValuePad\Api\Appraisal\V2_0\Support\MessagesTrait;
use ValuePad\Api\Appraisal\V2_0\Transformers\MessageTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Appraisal\Options\FetchMessagesOptions;
use ValuePad\Core\Appraisal\Services\MessageService;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class MessagesController extends BaseController
{
	use MessagesTrait;

	/**
	 * @var MessageService
	 */
	private $messageService;

	/**
	 * @param MessageService $messageService
	 */
	public function initialize(MessageService $messageService)
	{
		$this->messageService = $messageService;
	}

	/**
	 * @param MessagesSearchableProcessor $processor
	 * @param int $appraiserId
	 * @return Response
	 */
	public function index(MessagesSearchableProcessor $processor, $appraiserId)
	{
		$adapter = new DefaultPaginatorAdapter([
			'getAll' => function($page, $perPage) use ($appraiserId, $processor){
				$options = new FetchMessagesOptions();
				$options->setPagination(new PaginationOptions($page, $perPage));
				$options->setSortables($processor->createSortables());
				$options->setCriteria($processor->getCriteria());

				return $this->messageService->getAllByParticipantId($appraiserId, $options);
			},
			'getTotal' => function() use ($appraiserId, $processor){
				return $this->messageService->getTotalByParticipantId($appraiserId, $processor->getCriteria());
			}
		]);

		return $this->resource->makeAll(
			$this->paginator($adapter),
			$this->transformer(MessageTransformer::class)
		);
	}

	/**
	 * @param MessagesSearchableProcessor $processor
	 * @param int $appraiserId
	 * @param int $orderId
	 * @return Response
	 */
	public function indexByOrder(MessagesSearchableProcessor $processor, $appraiserId, $orderId)
	{
		$adapter = new DefaultPaginatorAdapter([
			'getAll' => function($page, $perPage) use ($orderId, $processor){
				$options = new FetchMessagesOptions();
				$options->setPagination(new PaginationOptions($page, $perPage));
				$options->setSortables($processor->createSortables());
				$options->setCriteria($processor->getCriteria());

				return $this->messageService->getAllByOrderId($orderId, $options);
			},
			'getTotal' => function() use ($orderId, $processor){
				return $this->messageService->getTotalByOrderId($orderId, $processor->getCriteria());
			}
		]);

		return $this->resource->makeAll(
			$this->paginator($adapter),
			$this->transformer(MessageTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @return Response
	 */
	public function markAllAsRead($appraiserId)
	{
		$this->messageService->markAllAsRead($appraiserId);

		return $this->resource->blank();
	}

	/**
	 * @param int $appraiserId
	 * @param int $messageId
	 * @return Response
	 */
	public function markAsRead($appraiserId, $messageId)
	{
		$this->tryMarkAsRead(function() use ($messageId, $appraiserId){
			$this->messageService->markAsRead([$messageId], $appraiserId);
		});

		return $this->resource->blank();
	}

	/**
	 * @param SelectedMessagesProcessor $processor
	 * @param int $appraiserId
	 * @return Response
	 */
	public function markSomeAsRead(SelectedMessagesProcessor $processor, $appraiserId)
	{
		$this->tryMarkAsRead(function() use ($processor, $appraiserId){
			$this->messageService->markAsRead($processor->getMessages(), $appraiserId);
		});

		return $this->resource->blank();
	}

	/**
	 * @param int $appraiserId
	 * @param int $messageId
	 * @return Response
	 */
	public function show($appraiserId, $messageId)
	{
		return $this->resource->make(
			$this->messageService->get($messageId),
			$this->transformer(MessageTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @param int $orderId
	 * @param MessagesProcessor $processor
	 * @return Response
	 */
	public function store($appraiserId, $orderId, MessagesProcessor $processor)
	{
		return $this->resource->make(
			$this->messageService->create($appraiserId, $orderId, $processor->createPersistable()),
			$this->transformer(MessageTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @return Response
	 */
	public function total($appraiserId)
	{
		return $this->resource->make([
			'total' => $this->messageService->getTotalByParticipantId($appraiserId),
			'unread' => $this->messageService->getTotalUnreadByParticipantId($appraiserId)
		]);
	}

	/**
	 * @param Action $action
	 * @param AppraiserService $appraiserService
	 * @param MessageService $messageService
	 * @param int $appraiserId
	 * @param int $orderOrMessageId
	 * @return bool
	 */
	public static function verifyAction(
		Action $action,
		AppraiserService $appraiserService,
		MessageService $messageService,
		$appraiserId,
		$orderOrMessageId = null
	)
	{
		if (!$appraiserService->exists($appraiserId)){
			return false;
		}

		if ($action->is(['store', 'indexByOrder'])){
			return $appraiserService->hasOrder($appraiserId, $orderOrMessageId, true);
		}

		if ($orderOrMessageId === null){
			return true;
		}

		return $messageService->isReadableByParticipantId($orderOrMessageId, $appraiserId);
	}
}
