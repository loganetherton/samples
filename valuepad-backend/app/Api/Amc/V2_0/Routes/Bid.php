<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\BidController;

class Bid implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->post('amcs/{amcId}/orders/{orderId}/bid', BidController::class.'@store');
        $registrar->get('amcs/{amcId}/orders/{orderId}/bid', BidController::class.'@show');
    }
}
