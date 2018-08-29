<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Shared\Processors\AvailabilityProcessor;
use ValuePad\Api\Shared\Transformers\MinimalAvailabilityPerCustomerTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraiser\Services\AppraiserService;
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
     * @param int $appraiserId
     * @param int $customerId
     * @return Response
     */
    public function show($appraiserId, $customerId)
    {
        return $this->resource->make(
            $this->availabilityPerCustomerService->getByUserAndCustomerId($appraiserId, $customerId),
            $this->transformer(MinimalAvailabilityPerCustomerTransformer::class)
        );
    }

    /**
     * @param AvailabilityProcessor $processor
     * @param int $appraiserId
     * @param int $customerId
     * @return Response
     */
    public function update(AvailabilityProcessor $processor, $appraiserId, $customerId)
    {
        $this->availabilityPerCustomerService->replace(
            $appraiserId,
            $customerId,
            $processor->createPersistable(),
            $processor->schedulePropertiesToClear()
        );

        return $this->resource->blank();
    }

    /**
     * @param AppraiserService $appraiserService
     * @param int $appraiserId
     * @param int $customerId
     * @return bool
     */
    public static function verifyAction(AppraiserService $appraiserService, $appraiserId, $customerId)
    {
        if (!$appraiserService->exists($appraiserId)) {
            return false;
        }

        if (!$appraiserService->isRelatedWithCustomer($appraiserId, $customerId)) {
            return false;
        }

        return true;
    }
}
