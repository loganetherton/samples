<?php
namespace ValuePad\Api\Company\V2_0\Controllers;
use Ascope\Libraries\Validation\PresentableException;
use Illuminate\Container\Container;
use ValuePad\Core\Company\Services\StaffService;

trait CompanyOrdersTrait
{
    /**
     * @param int $managerId
     * @param int $appraiserId
     * @param Container $container
     */
    protected function validateOrderReassignment($managerId, $appraiserId, Container $container)
    {
        /**
         * @var StaffService $staffService
         */
        $staffService = $container->make(StaffService::class);

        if (!$staffService->isManagerFor($managerId, $appraiserId)){
            throw new PresentableException('The "'.$managerId.'" manager and "'.$appraiserId.'" are not from the same company.');
        }
    }
}
