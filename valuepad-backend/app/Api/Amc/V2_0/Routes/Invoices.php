<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\InvoicesController;

class Invoices implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get('amcs/{amcId}/invoices', InvoicesController::class.'@index');
        $registrar->post('amcs/{amcId}/invoices/{invoiceId}/pay', InvoicesController::class.'@pay');
    }
}
