<?php
namespace ValuePad\Api\Company\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\ManagerSettingsController;

class ManagerSettings implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get('managers/{managerId}/settings',
            ManagerSettingsController::class.'@show');

        $registrar->patch('managers/{managerId}/settings',
            ManagerSettingsController::class.'@update');
    }
}
