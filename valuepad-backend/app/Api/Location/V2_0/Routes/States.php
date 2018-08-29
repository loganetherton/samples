<?php
namespace ValuePad\Api\Location\V2_0\Routes;

use Ascope\Libraries\Routing\Router;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Location\V2_0\Controllers\StatesController;

class States implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface|Router $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('/location/states', StatesController::class, [
            'only' => 'index'
        ]);

        $registrar->get('/location/states/{code}/zips', StatesController::class.'@zips')
            ->where('code', '...state');
    }
}
