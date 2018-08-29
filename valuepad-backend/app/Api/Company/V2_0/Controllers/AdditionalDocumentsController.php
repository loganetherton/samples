<?php
namespace ValuePad\Api\Company\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\AdditionalDocumentsProcessor;
use ValuePad\Api\Appraisal\V2_0\Processors\AdditionalDocumentsSearchableProcessor;
use ValuePad\Api\Appraisal\V2_0\Transformers\AdditionalDocumentTransformer;
use ValuePad\Api\Customer\V2_0\Transformers\AdditionalDocumentTypeTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Options\FetchAdditionalDocumentsOptions;
use ValuePad\Core\Appraisal\Services\AdditionalDocumentService;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Company\Services\ManagerService;
use ValuePad\Core\Support\Criteria\Sorting\Direction;
use ValuePad\Core\Support\Criteria\Sorting\Sortable;

class AdditionalDocumentsController extends BaseController
{
    /**
     * @param AdditionalDocumentService $additionalDocumentService
     */
    public function initialize(AdditionalDocumentService $additionalDocumentService)
    {
        $this->additionalDocumentService = $additionalDocumentService;
    }

    /**
     * @var AdditionalDocumentService
     */
    private $additionalDocumentService;

    /**
     * @param AdditionalDocumentsSearchableProcessor $processor
     * @param int $managerId
     * @param int $orderId
     * @return Response
     */
    public function index(
        AdditionalDocumentsSearchableProcessor $processor,
        $managerId,
        $orderId
    )
    {
        $options = new FetchAdditionalDocumentsOptions();

        $sortables = $processor->createSortables();

        if (!$sortables){
            $sortables[] = new Sortable('createdAt', new Direction(Direction::DESC));
        }

        $options->setSortables($sortables);

        return $this->resource->makeAll(
            $this->additionalDocumentService->getAll($orderId, $options),
            $this->transformer(AdditionalDocumentTransformer::class)
        );
    }

    /**
     * @param AdditionalDocumentsProcessor $processor
     * @param int $managerId
     * @param int $orderId
     * @return Response
     */
    public function store(AdditionalDocumentsProcessor $processor, $managerId, $orderId)
    {
        return $this->resource->make(
            $this->additionalDocumentService->create($orderId, $processor->createPersistable()),
            $this->transformer(AdditionalDocumentTransformer::class)
        );
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @return Response
     */
    public function types($managerId, $orderId)
    {
        return $this->resource->makeAll(
            $this->additionalDocumentService->getTypes($orderId),
            $this->transformer(AdditionalDocumentTypeTransformer::class)
        );
    }

    /**
     * @param ManagerService $managerService
     * @param OrderService $orderService
     * @param int $managerId
     * @param int $orderId
     * @param int $documentId
     * @return bool
     */
    public static function verifyAction(
        ManagerService $managerService,
        OrderService $orderService,
        $managerId,
        $orderId,
        $documentId = null
    )
    {
        if (!$managerService->exists($managerId)){
            return false;
        }

        if (!$managerService->hasOrder($managerId, $orderId)){
            return false;
        }

        if ($documentId === null){
            return true;
        }

        return $orderService->hasAdditionalDocument($orderId, $documentId);
    }
}
