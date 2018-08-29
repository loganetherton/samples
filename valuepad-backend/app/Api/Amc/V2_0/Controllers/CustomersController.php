<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\CustomersSearchableProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\AdditionalDocumentTypeTransformer;
use ValuePad\Api\Customer\V2_0\Transformers\AdditionalStatusTransformer;
use ValuePad\Api\Customer\V2_0\Transformers\CustomerTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Customer\Options\FetchCustomerOptions;
use ValuePad\Core\Customer\Services\AdditionalDocumentTypeService;
use ValuePad\Core\Customer\Services\AdditionalStatusService;
use ValuePad\Core\Customer\Services\CustomerService;

class CustomersController extends BaseController
{
    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @param CustomerService $customerService
     */
    public function initialize(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * @param int $amcId
     * @param CustomersSearchableProcessor $processor
     * @return Response
     */
    public function index($amcId, CustomersSearchableProcessor $processor)
    {
        $options = new FetchCustomerOptions();

        $options->setSortables($processor->createSortables());

        return $this->resource->makeAll(
            $this->customerService->getAllByAmcId($amcId, $options),
            $this->transformer(CustomerTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @return Response
     */
    public function listAdditionalStatuses($amcId, $customerId, AdditionalStatusService $service)
    {
        return $this->resource->makeAll(
            $service->getAllActive($customerId),
            $this->transformer(AdditionalStatusTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @return Response
     */
    public function listAdditionalDocumentsTypes($amcId, $customerId, AdditionalDocumentTypeService $service)
    {
        return $this->resource->makeAll(
            $service->getAll($customerId),
            $this->transformer(AdditionalDocumentTypeTransformer::class)
        );
    }

    /**
     * @param AmcService $amcService
     * @param int $amcId
     * @param int $customerId
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, $amcId, $customerId = null)
    {
        if (!$amcService->exists($amcId)) {
            return false;
        }

        if ($customerId && !$amcService->isRelatedWithCustomer($amcId, $customerId)) {
            return false;
        }

        return true;
    }
}
