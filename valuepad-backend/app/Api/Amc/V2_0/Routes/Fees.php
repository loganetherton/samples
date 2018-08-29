<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\CustomerFeesController;
use ValuePad\Api\Amc\V2_0\Controllers\FeesController;

class Fees implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('amcs.customers.fees', CustomerFeesController::class, [
            'except' => 'shows'
        ]);

        $registrar->patch('amcs/{amcId}/customers/{customerId}/fees',
            CustomerFeesController::class.'@updateBulk');

        $registrar->delete('amcs/{amcId}/customers/{customerId}/fees',
            CustomerFeesController::class.'@destroyBulk');

        $registrar->get('amcs/{amcId}/customers/{customerId}/fees/job-types/{jobTypeId}/zips/{zip}',
            CustomerFeesController::class.'@showFeeByZip');

        $registrar->put('amcs/{amcId}/customers/{customerId}/fees/apply-default-location-fees',
            CustomerFeesController::class.'@applyDefaultLocationFees');

        $registrar->get('amcs/{amcId}/fees', FeesController::class.'@index');
        $registrar->put('amcs/{amcId}/fees', FeesController::class.'@sync');

        $registrar->get('amcs/{amcId}/fees/totals', FeesController::class.'@totals');
    }
}
