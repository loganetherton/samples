<?php
namespace ValuePad\Api\Company\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\BranchesController;

class Branches implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get('companies/{companyId}/branches', BranchesController::class.'@index');
        $registrar->post('companies/{companyId}/branches', BranchesController::class.'@store');
        $registrar->patch('companies/{companyId}/branches/{branchId}', BranchesController::class.'@update');
        $registrar->delete('companies/{companyId}/branches/{branchId}', BranchesController::class.'@destroy');
    }
}
