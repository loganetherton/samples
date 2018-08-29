<?php
namespace ValuePad\Api\Company\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\StaffController;

class Staff implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('companies.staff', StaffController::class, ['only' => ['index', 'show', 'update', 'destroy']]);

        $registrar->post('companies/{companyId}/managers', StaffController::class.'@storeManager');
        $registrar->get('companies/{companyId}/branches/{branchId}/staff', StaffController::class.'@indexByBranch');
    }
}
