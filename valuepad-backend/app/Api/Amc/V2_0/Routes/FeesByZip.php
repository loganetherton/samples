<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Ascope\Libraries\Routing\Router;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\CustomerFeesByZipController;
use ValuePad\Api\Amc\V2_0\Controllers\FeesByZipController;

class FeesByZip implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface|Router $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get(
            'amcs/{amcId}/fees/{jobTypeId}/states/{code}/zips',
            FeesByZipController::class.'@index'
        )->where('code', '...state');

        $registrar->put(
            'amcs/{amcId}/fees/{jobTypeId}/states/{code}/zips',
            FeesByZipController::class.'@sync'
        )->where('code', '...state');

        $registrar->get(
            'amcs/{amcId}/customers/{customerId}/fees/{jobTypeId}/states/{code}/zips',
            CustomerFeesByZipController::class.'@index'
        )->where('code', '...state');

        $registrar->put(
            'amcs/{amcId}/customers/{customerId}/fees/{jobTypeId}/states/{code}/zips',
            CustomerFeesByZipController::class.'@sync'
        )->where('code', '...state');
    }
}
