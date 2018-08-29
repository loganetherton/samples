<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\CreditCardController;

class CreditCard implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->put('amcs/{amcId}/payment/credit-card', CreditCardController::class.'@replace');
        $registrar->get('amcs/{amcId}/payment/credit-card', CreditCardController::class.'@show');
    }
}
