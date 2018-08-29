<?php
namespace ValuePad\Api\Company\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\ManagersController;

class Managers implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('managers', ManagersController::class, ['only' => ['show', 'update']]);

        $registrar->patch('managers/{managerId}/availability',ManagersController::class.'@updateAvailability');
    }
}
