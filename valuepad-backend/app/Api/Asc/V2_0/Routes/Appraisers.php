<?php
namespace ValuePad\Api\Asc\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Asc\V2_0\Controllers\AppraisersController;

class Appraisers implements RouteRegistrarInterface
{

    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('asc', AppraisersController::class, [
            'only' => 'index'
        ]);
    }
}
