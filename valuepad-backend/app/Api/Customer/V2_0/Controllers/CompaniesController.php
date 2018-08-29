<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\OrdersProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Options\CreateOrderOptions;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Customer\Services\CustomerService;

class CompaniesController extends BaseController
{
    /**
     * @param int $customerId
     * @param int $companyId
     * @param int $staffId
     * @param OrdersProcessor $processor
     * @return Response
     */
    public function storeOrder($customerId, $companyId, $staffId, OrdersProcessor $processor)
    {
        $options = new CreateOrderOptions();
        $options->setCompanyId($companyId)
            ->setFromStaff(true);

        /**
         * @var OrderService $orderService
         */
        $orderService = $this->container->make(OrderService::class);

        return $this->resource->make(
            $orderService->create($customerId, $staffId, $processor->createPersistable(), $options),
            $this->transformer()
        );
    }

    /**
     * @param CustomerService $customerService
     * @param CompanyService $companyService
     * @param int $customerId
     * @param int $companyId
     * @param int $staffId
     * @return bool
     */
    public static function verifyAction(
        CustomerService $customerService,
        CompanyService $companyService,
        $customerId,
        $companyId,
        $staffId
    )
    {
        if (!$customerService->exists($customerId)){
            return false;
        }

        if (!$companyService->exists($companyId)){
            return false;
        }

        return $companyService->hasStaff($companyId, $staffId);
    }
}
