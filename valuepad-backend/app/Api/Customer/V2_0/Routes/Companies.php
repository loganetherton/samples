<?php
namespace ValuePad\Api\Customer\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Customer\V2_0\Controllers\CompaniesController;

class Companies implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->post(
            '/customers/{customerId}/companies/{companyId}/staff/{staffId}/orders',
            CompaniesController::class.'@storeOrder'
        );
    }
}
