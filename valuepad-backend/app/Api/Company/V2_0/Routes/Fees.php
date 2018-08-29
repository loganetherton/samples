<?php
namespace ValuePad\Api\Company\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\FeesController;

class Fees implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get('companies/{companyId}/fees', FeesController::class.'@index');
        $registrar->post('companies/{companyId}/fees', FeesController::class.'@store');
        $registrar->put('companies/{companyId}/fees', FeesController::class.'@replace');
    }
}
