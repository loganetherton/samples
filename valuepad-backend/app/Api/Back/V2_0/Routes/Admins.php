<?php
namespace ValuePad\Api\Back\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Back\V2_0\Controllers\AdminsController;

class Admins implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('admins', AdminsController::class, ['except' => ['index', 'destroy']]);
    }
}
