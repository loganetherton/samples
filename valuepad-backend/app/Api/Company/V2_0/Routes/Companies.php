<?php
namespace ValuePad\Api\Company\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\CompaniesController;

class Companies implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get('companies/tax-id/{taxId}', CompaniesController::class.'@showByTaxId')
            ->where('taxId', '[0-9]{2}-[0-9]{7}');

        $registrar->post('companies', CompaniesController::class.'@store');
        $registrar->patch('companies/{companyId}', CompaniesController::class.'@update');
    }
}
