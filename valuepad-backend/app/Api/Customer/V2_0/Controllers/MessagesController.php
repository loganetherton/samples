<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use Ascope\Libraries\Verifier\Action;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\MessagesSearchableProcessor;
use ValuePad\Api\Appraisal\V2_0\Processors\SelectedMessagesProcessor;
use ValuePad\Api\Appraisal\V2_0\Support\MessagesTrait;
use ValuePad\Api\Appraisal\V2_0\Transformers\MessageTransformer;
use ValuePad\Api\Customer\V2_0\Processors\MessagesProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Appraisal\Options\FetchMessagesOptions;
use ValuePad\Core\Appraisal\Services\MessageService;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Customer\Services\MessageFactoryService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class MessagesController extends BaseController
{
	use MessagesTrait;

	/**
	 * @var MessageFactoryService
	 */
	private $factory;

	/**
	 * @var MessageService
	 */
	private $messageService;

	/**
	 * @param MessageService $messageService
	 * @param MessageFactoryService $factory
	 */
	public function initialize(MessageService $messageService, MessageFactoryService $factory)
	{
		$this->messageService = $messageService;
		$this->factory = $factory;
	}

	/**
	 * @param MessagesSearchableProcessor $processor
	 * @param int $customerId
	 * @return Response
	 */
	public function index(MessagesSearchableProcessor $processor, $customerId)
	{
		$adapter = new DefaultPaginatorAdapter([
			'getAll' => function($page, $perPage) use ($customerId, $processor){
				$options = new FetchMessagesOptions();
				$options->setPagination(new PaginationOptions($page, $perPage));
				$options->setSortables($processor->createSortables());
				$options->setCriteria($processor->getCriteria());

				return $this->messageService->getAllByParticipantId($customerId, $options);
			},
			'getTotal' => function() use ($customerId, $processor){
				return $this->messageService->getTotalByParticipantId($customerId, $processor->getCriteria());
			}
		]);

		return $this->resource->makeAll(
			$this->paginator($adapter),
			$this->transformer(MessageTransformer::class)
		);
	}

	/**
	 * @param MessagesSearchableProcessor $processor
	 * @param int $customerId
	 * @param int $orderId
	 * @return Response
	 */
	public function indexByOrder(MessagesSearchableProcessor $processor, $customerId, $orderId)
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
	 * @param int $customerId
	 * @return Response
	 */
	public function markAllAsRead($customerId)
	{
		$this->messageService->markAllAsRead($customerId);

		return $this->resource->blank();
	}

	/**
	 * @param int $customerId
	 * @param int $messageId
	 * @return Response
	 */
	public function markAsRead($customerId, $messageId)
	{
		$this->tryMarkAsRead(function() use ($messageId, $customerId){
			$this->messageService->markAsRead([$messageId], $customerId);
		});

		return $this->resource->blank();
	}

	/**
	 * @param SelectedMessagesProcessor $processor
	 * @param int $readerId
	 * @return Response
	 */
	public function markSomeAsRead(SelectedMessagesProcessor $processor, $readerId)
	{
		$this->tryMarkAsRead(function() use ($processor, $readerId){
			$this->messageService->markAsRead($processor->getMessages(), $readerId);
		});

		return $this->resource->blank();
	}

	/**
	 * @param int $customerId
	 * @param int $messageId
	 * @return Response
	 */
	public function show($customerId, $messageId)
	{
		return $this->resource->make(
			$this->messageService->get($messageId),
			$this->transformer(MessageTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $orderId
	 * @param MessagesProcessor $processor
	 * @return Response
	 */
	public function store($customerId, $orderId, MessagesProcessor $processor)
	{
		return $this->resource->make(
			$this->factory->create($customerId, $orderId, $processor->createPersistable()),
			$this->transformer(MessageTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $messageId
	 * @return Response
	 */
	public function destroy($customerId, $messageId)
	{
		$this->messageService->deleteBySenderId($messageId, $customerId);

		return $this->resource->blank();
	}

	/**
	 * @param int $customerId
	 * @param SelectedMessagesProcessor $processor
	 * @return Response
	 */
	public function destroyAll($customerId, SelectedMessagesProcessor $processor)
	{
		$this->messageService->deleteSelectedBySenderId($processor->getMessages(), $customerId);

		return $this->resource->blank();
	}

	/**
	 * @param Action $action
	 * @param CustomerService $customerService
	 * @param MessageService $messageService
	 * @param int $customerId
	 * @param int $orderOrMessageId
	 * @return bool
	 */
	public static function verifyAction(
		Action $action,
		CustomerService $customerService,
		MessageService $messageService,
		$customerId,
		$orderOrMessageId = null
	)
	{
		if (!$customerService->exists($customerId)){
			return false;
		}

		if ($action->is(['store', 'indexByOrder'])){
			return $customerService->hasOrder($customerId, $orderOrMessageId);
		}

		if ($orderOrMessageId === null){
			return true;
		}

		return $messageService->isReadableByParticipantId($orderOrMessageId, $customerId);
	}

	/**
	 * @return MessageService
	 */
	protected function getMessageService()
	{
		return $this->messageService;
	}
}
