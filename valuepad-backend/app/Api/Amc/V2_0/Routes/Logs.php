<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\LogsController;

class Logs implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get('amcs/{amcId}/logs', LogsController::class.'@index');
        $registrar->get('amcs/{amcId}/logs/{logId}', LogsController::class.'@show');
        $registrar->get('amcs/{amcId}/orders/{orderId}/logs', LogsController::class.'@indexByOrder');
    }
}
