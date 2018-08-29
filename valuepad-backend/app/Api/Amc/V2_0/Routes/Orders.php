<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\OrdersController;

class Orders implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('amcs.orders', OrdersController::class, ['only' => ['index', 'show', 'destroy']]);

        $registrar->post('amcs/{amcId}/orders/{orderId}/accept', OrdersController::class.'@accept');

        $registrar->post(
            'amcs/{amcId}/orders/{orderId}/accept-with-conditions',
            OrdersController::class.'@acceptWithConditions'
        );

        $registrar->post('amcs/{amcId}/orders/{orderId}/decline', OrdersController::class.'@decline');

        $registrar->post('amcs/{amcId}/orders/{orderId}/change-additional-status',
            OrdersController::class.'@changeAdditionalStatus');

        $registrar->get('amcs/{amcId}/orders/{orderId}/additional-statuses',
            OrdersController::class.'@listAdditionalStatuses');

        $registrar->get('amcs/{amcId}/orders/totals', OrdersController::class.'@totals');

    }
}
