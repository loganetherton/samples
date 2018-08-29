<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\DocumentController;

class Document implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get(
            'amcs/{amcId}/orders/{orderId}/document/formats',
            DocumentController::class.'@formats'
        );

        $registrar->post(
            'amcs/{amcId}/orders/{orderId}/document',
            DocumentController::class.'@store'
        );

        $registrar->get(
            'amcs/{amcId}/orders/{orderId}/document',
            DocumentController::class.'@show'
        );

        $registrar->patch(
            'amcs/{amcId}/orders/{orderId}/document',
            DocumentController::class.'@update'
        );
    }
}
