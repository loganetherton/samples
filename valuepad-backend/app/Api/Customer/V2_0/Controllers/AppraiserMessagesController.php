<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\MessagesSearchableProcessor;
use ValuePad\Api\Appraisal\V2_0\Transformers\MessageTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Appraisal\Options\FetchMessagesOptions;
use ValuePad\Core\Appraisal\Services\MessageService;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class AppraiserMessagesController extends BaseController
{
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
     * @param int $customerId
     * @param int $appraiserId
     * @param MessagesSearchableProcessor $processor
     * @return Response
     */
    public function index($customerId, $appraiserId, MessagesSearchableProcessor $processor)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($customerId, $appraiserId, $processor){
                $options = new FetchMessagesOptions();
                $options->setPagination(new PaginationOptions($page, $perPage));
                $options->setSortables($processor->createSortables());
                $options->setCriteria($processor->getCriteria());

                return $this->messageService->getAllByCustomerAndAssigneeIds($customerId, $appraiserId, $options);
            },
            'getTotal' => function() use ($customerId, $appraiserId, $processor){
                return $this->messageService->getTotalByCustomerAndAssigneeIds($customerId, $appraiserId, $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll(
            $this->paginator($adapter),
            $this->transformer(MessageTransformer::class)
        );
    }

    /**
     * @param int $customerId
     * @param int $appraiserId
     * @return Response
     */
    public function total($customerId, $appraiserId)
    {
        return $this->resource->make([
            'total' => $this->messageService->getTotalByCustomerAndAssigneeIds($customerId, $appraiserId),
            'unread' => $this->messageService->getTotalUnreadByCustomerAndAssigneeIds($customerId, $appraiserId)
        ]);
    }

    /**
     * @param CustomerService $customerService
     * @param int $customerId
     * @param int $appraiserId
     * @return bool
     */
    public static function verifyAction(CustomerService $customerService, $customerId, $appraiserId)
    {
        if (!$customerService->exists($customerId)){
            return false;
        }

        return $customerService->isRelatedWithAppraiser($customerId, $appraiserId);
    }
}
