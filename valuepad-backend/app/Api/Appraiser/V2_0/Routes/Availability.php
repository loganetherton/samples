<?php
namespace ValuePad\Api\Appraiser\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Appraiser\V2_0\Controllers\AvailabilityController;

class Availability implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get(
            'appraisers/{appraiserId}/customers/{customerId}/availability',
            AvailabilityController::class.'@show'
        );
        $registrar->patch(
            'appraisers/{appraiserId}/customers/{customerId}/availability',
            AvailabilityController::class.'@update'
        );
    }
}
