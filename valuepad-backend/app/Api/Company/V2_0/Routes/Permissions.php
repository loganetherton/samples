<?php
namespace ValuePad\Api\Company\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\PermissionsController;

class Permissions implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get('/companies/{companyId}/staff/{staffId}/permissions', PermissionsController::class.'@index');
        $registrar->put('/companies/{companyId}/staff/{staffId}/permissions', PermissionsController::class.'@replace');
    }
}
