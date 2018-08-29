<?php
namespace ValuePad\Api\Amc\V2_0\Protectors;

use ValuePad\Api\Assignee\V2_0\Protectors\AbstractCustomerProtector;
use ValuePad\Core\Customer\Services\CustomerService;

class CustomerProtector extends AbstractCustomerProtector
{
    /**
     * @param int $customerId
     * @param int $assigneeId
     * @return bool
     */
    function isRelated($customerId, $assigneeId)
    {
        /**
         * @var CustomerService $customerService
         */
        $customerService = $this->container->make(CustomerService::class);

        return $customerService->isRelatedWithAmc($customerId, $assigneeId);
    }
}
