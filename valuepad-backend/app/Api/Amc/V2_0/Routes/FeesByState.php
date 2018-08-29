<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Ascope\Libraries\Routing\Router;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\CustomerFeesByStateController;
use ValuePad\Api\Amc\V2_0\Controllers\FeesByStateController;

class FeesByState implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface|Router $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get('amcs/{amcId}/fees/{jobTypeId}/states', FeesByStateController::class.'@index');
        $registrar->put('amcs/{amcId}/fees/{jobTypeId}/states', FeesByStateController::class.'@sync');

        $registrar->patch(
            'amcs/{amcId}/fees/{jobTypeId}/states/{code}', FeesByStateController::class.'@update')
            ->where('code', '...state');

        $registrar->get(
            'amcs/{amcId}/customers/{customerId}/fees/{jobTypeId}/states',
            CustomerFeesByStateController::class.'@index'
        );
        $registrar->put(
            'amcs/{amcId}/customers/{customerId}/fees/{jobTypeId}/states',
            CustomerFeesByStateController::class.'@sync'
        );
        $registrar->patch(
            'amcs/{amcId}/customers/{customerId}/fees/{jobTypeId}/states/{code}',
            CustomerFeesByStateController::class.'@update'
        )->where('code', '...state');
    }
}
