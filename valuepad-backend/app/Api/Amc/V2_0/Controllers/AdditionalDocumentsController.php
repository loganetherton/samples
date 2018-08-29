<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\AdditionalDocumentsProcessor;
use ValuePad\Api\Appraisal\V2_0\Processors\AdditionalDocumentsSearchableProcessor;
use ValuePad\Api\Appraisal\V2_0\Transformers\AdditionalDocumentTransformer;
use ValuePad\Api\Customer\V2_0\Transformers\AdditionalDocumentTypeTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraisal\Options\FetchAdditionalDocumentsOptions;
use ValuePad\Core\Appraisal\Services\AdditionalDocumentService;
use ValuePad\Core\Support\Criteria\Sorting\Direction;
use ValuePad\Core\Support\Criteria\Sorting\Sortable;


class AdditionalDocumentsController extends BaseController
{
    /**
     * @var AdditionalDocumentService
     */
    private $additionalDocumentService;

    /**
     * @param AdditionalDocumentService $additionalDocumentService
     */
    public function initialize(AdditionalDocumentService $additionalDocumentService)
    {
        $this->additionalDocumentService = $additionalDocumentService;
    }

    /**
     * @param AdditionalDocumentsSearchableProcessor $processor
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function index(AdditionalDocumentsSearchableProcessor $processor, $amcId, $orderId)
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
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function store(AdditionalDocumentsProcessor $processor, $amcId, $orderId)
    {
        return $this->resource->make(
            $this->additionalDocumentService->create($orderId, $processor->createPersistable()),
            $this->transformer(AdditionalDocumentTransformer::class)
        );
    }


    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function types($amcId, $orderId)
    {
        return $this->resource->makeAll(
            $this->additionalDocumentService->getTypes($orderId),
            $this->transformer(AdditionalDocumentTypeTransformer::class)
        );
    }

    /**
     * @param AmcService $amcService
     * @param int $amcId
     * @param int $orderId
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, $amcId, $orderId)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

       return $amcService->hasOrder($amcId, $orderId);
    }
}
