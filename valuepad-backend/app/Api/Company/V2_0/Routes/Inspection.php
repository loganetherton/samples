<?php
namespace ValuePad\Api\Company\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\InspectionController;

class Inspection implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->post(
            'managers/{managerId}/orders/{orderId}/schedule-inspection',
            InspectionController::class.'@schedule'
        );

        $registrar->post(
            'managers/{managerId}/orders/{orderId}/complete-inspection',
            InspectionController::class.'@complete'
        );
    }
}
