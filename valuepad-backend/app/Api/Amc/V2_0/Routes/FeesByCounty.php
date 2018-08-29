<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Ascope\Libraries\Routing\Router;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\CustomerFeesByCountyController;
use ValuePad\Api\Amc\V2_0\Controllers\FeesByCountyController;

class FeesByCounty implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface|Router $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get(
            'amcs/{amcId}/fees/{jobTypeId}/states/{code}/counties',
            FeesByCountyController::class.'@index'
        )->where('code', '...state');

        $registrar->put(
            'amcs/{amcId}/fees/{jobTypeId}/states/{code}/counties',
            FeesByCountyController::class.'@sync'
        )->where('code', '...state');


        $registrar->get(
            'amcs/{amcId}/customers/{customerId}/fees/{feeId}/states/{code}/counties',
            CustomerFeesByCountyController::class.'@index'
        )->where('code', '...state');

        $registrar->put(
            'amcs/{amcId}/customers/{customerId}/fees/{feeId}/states/{code}/counties',
            CustomerFeesByCountyController::class.'@sync'
        )->where('code', '...state');
    }
}
