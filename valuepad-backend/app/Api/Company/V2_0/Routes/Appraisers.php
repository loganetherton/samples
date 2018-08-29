<?php
namespace ValuePad\Api\Company\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\AppraisersController;

class Appraisers implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get(
            'companies/{companyId}/appraisers',
            AppraisersController::class.'@index'
        );
        $registrar->get(
            'companies/{companyId}/appraisers/{appraiserId}',
            AppraisersController::class.'@show'
        );
        $registrar->patch(
            'companies/{companyId}/appraisers/{appraiserId}',
            AppraisersController::class.'@update'
        );
    }
}
