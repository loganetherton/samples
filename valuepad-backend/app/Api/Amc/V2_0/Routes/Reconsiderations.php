<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\ReconsiderationsController;

class Reconsiderations implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('amcs.orders.reconsiderations', ReconsiderationsController::class, ['only' => ['index']]);
        $registrar->get('amcs/{amcId}/reconsiderations/{reconsiderationId}', ReconsiderationsController::class.'@show');
        $registrar->get('amcs/{amcId}/orders/{orderId}/reconsiderations/{reconsiderationId}', ReconsiderationsController::class.'@showByOrder');
    }
}
