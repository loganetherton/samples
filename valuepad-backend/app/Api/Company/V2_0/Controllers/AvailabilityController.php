<?php
namespace ValuePad\Api\Company\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Shared\Processors\AvailabilityProcessor;
use ValuePad\Api\Shared\Transformers\MinimalAvailabilityPerCustomerTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Company\Services\ManagerService;
use ValuePad\Core\Shared\Services\AvailabilityPerCustomerService;

class AvailabilityController extends BaseController
{
    /**
     * @var AvailabilityPerCustomerService
     */
    private $availabilityPerCustomerService;

    /**
     * @param AvailabilityPerCustomerService $availabilityPerCustomerService
     */
    public function initialize(AvailabilityPerCustomerService $availabilityPerCustomerService)
    {
        $this->availabilityPerCustomerService = $availabilityPerCustomerService;
    }

    /**
     * @param int $managerId
     * @param int $customerId
     * @return Response
     */
    public function show($managerId, $customerId)
    {
        return $this->resource->make(
            $this->availabilityPerCustomerService->getByUserAndCustomerId($managerId, $customerId),
            $this->transformer(MinimalAvailabilityPerCustomerTransformer::class)
        );
    }

    /**
     * @param AvailabilityProcessor $processor
     * @param int $managerId
     * @param int $customerId
     * @return Response
     */
    public function replace(AvailabilityProcessor $processor, $managerId, $customerId)
    {
        $this->availabilityPerCustomerService->replace(
            $managerId,
            $customerId,
            $processor->createPersistable(),
            $processor->schedulePropertiesToClear()
        );

        return $this->resource->blank();
    }

    /**
     * @param ManagerService $managerService
     * @param int $managerId
     * @param int $customerId
     * @return bool
     */
    public static function verifyAction(ManagerService $managerService, $managerId, $customerId)
    {
        if (! $managerService->exists($managerId)) {
            return false;
        }

        if (! $managerService->isRelatedWithCustomer($managerId, $customerId)) {
            return false;
        }

        return true;
    }
}
