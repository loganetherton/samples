<?php
namespace ValuePad\Api\Company\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\AvailabilityController;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;

class Availability implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get(
            'managers/{managerId}/customers/{customerId}/availability',
            AvailabilityController::class.'@show'
        );
        $registrar->patch(
            'managers/{managerId}/customers/{customerId}/availability',
            AvailabilityController::class.'@replace'
        );
    }
}
