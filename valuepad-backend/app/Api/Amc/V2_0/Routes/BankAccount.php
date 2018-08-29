<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\BankAccountController;

class BankAccount implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->put('amcs/{amcId}/payment/bank-account', BankAccountController::class.'@change');
        $registrar->get('amcs/{amcId}/payment/bank-account', BankAccountController::class.'@show');
    }
}
