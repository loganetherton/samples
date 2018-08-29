<?php
namespace ValuePad\Api\Company\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\CustomersSearchableProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\CustomerTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Company\Services\ManagerService;
use ValuePad\Core\Customer\Options\FetchCustomerOptions;
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
     * @param CustomersSearchableProcessor $processor
     * @param int $managerId
     * @return Response
     */
    public function index(CustomersSearchableProcessor $processor, $managerId)
    {
        $options = new FetchCustomerOptions();
        $options->setSortables($processor->createSortables());

        return $this->resource->makeAll(
            $this->customerService->getAllByManagerId($managerId, $options),
            $this->transformer(CustomerTransformer::class)
        );
    }

    /**
     * @param ManagerService $managerService
     * @param int $managerId
     * @return bool
     */
    public static function verifyAction(ManagerService $managerService, $managerId)
    {
        return $managerService->exists($managerId);
    }
}
