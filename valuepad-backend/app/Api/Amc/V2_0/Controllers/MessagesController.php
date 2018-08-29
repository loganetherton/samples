<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Ascope\Libraries\Verifier\Action;
use ValuePad\Api\Appraisal\V2_0\Processors\MessagesProcessor;
use ValuePad\Api\Appraisal\V2_0\Processors\MessagesSearchableProcessor;
use ValuePad\Api\Appraisal\V2_0\Processors\SelectedMessagesProcessor;
use ValuePad\Api\Appraisal\V2_0\Support\MessagesTrait;
use ValuePad\Api\Appraisal\V2_0\Transformers\MessageTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraisal\Options\FetchMessagesOptions;
use ValuePad\Core\Appraisal\Services\MessageService;
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
     * @param int $amcId
     * @return Response
     */
    public function index(MessagesSearchableProcessor $processor, $amcId)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($amcId, $processor){
                $options = new FetchMessagesOptions();
                $options->setPagination(new PaginationOptions($page, $perPage));
                $options->setSortables($processor->createSortables());
                $options->setCriteria($processor->getCriteria());

                return $this->messageService->getAllByParticipantId($amcId, $options);
            },
            'getTotal' => function() use ($amcId, $processor){
                return $this->messageService->getTotalByParticipantId($amcId, $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll(
            $this->paginator($adapter),
            $this->transformer(MessageTransformer::class)
        );
    }

    /**
     * @param MessagesSearchableProcessor $processor
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function indexByOrder(MessagesSearchableProcessor $processor, $amcId, $orderId)
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
     * @param int $amcId
     * @return Response
     */
    public function markAllAsRead($amcId)
    {
        $this->messageService->markAllAsRead($amcId);

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $messageId
     * @return Response
     */
    public function markAsRead($amcId, $messageId)
    {
        $this->tryMarkAsRead(function() use ($messageId, $amcId){
            $this->messageService->markAsRead([$messageId], $amcId);
        });

        return $this->resource->blank();
    }

    /**
     * @param SelectedMessagesProcessor $processor
     * @param int $amcId
     * @return Response
     */
    public function markSomeAsRead(SelectedMessagesProcessor $processor, $amcId)
    {
        $this->tryMarkAsRead(function() use ($processor, $amcId){
            $this->messageService->markAsRead($processor->getMessages(), $amcId);
        });

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $messageId
     * @return Response
     */
    public function show($amcId, $messageId)
    {
        return $this->resource->make(
            $this->messageService->get($messageId),
            $this->transformer(MessageTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @param MessagesProcessor $processor
     * @return Response
     */
    public function store($amcId, $orderId, MessagesProcessor $processor)
    {
        return $this->resource->make(
            $this->messageService->create($amcId, $orderId, $processor->createPersistable()),
            $this->transformer(MessageTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @return Response
     */
    public function total($amcId)
    {
        return $this->resource->make([
            'total' => $this->messageService->getTotalByParticipantId($amcId),
            'unread' => $this->messageService->getTotalUnreadByParticipantId($amcId)
        ]);
    }

    /**
     * @param Action $action
     * @param AmcService $amcService
     * @param MessageService $messageService
     * @param int $amcId
     * @param int $orderOrMessageId
     * @return bool
     */
    public static function verifyAction(
        Action $action,
        AmcService $amcService,
        MessageService $messageService,
        $amcId,
        $orderOrMessageId = null
    )
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        if ($action->is(['store', 'indexByOrder'])){
            return $amcService->hasOrder($amcId, $orderOrMessageId);
        }

        if ($orderOrMessageId === null){
            return true;
        }

        return $messageService->isReadableByParticipantId($orderOrMessageId, $amcId);
    }
}
