<?php
namespace ValuePad\Api\Company\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\BidController;

class Bid implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->post('managers/{managerId}/orders/{orderId}/bid', BidController::class.'@store');
        $registrar->get('managers/{managerId}/orders/{orderId}/bid', BidController::class.'@show');
    }
}
