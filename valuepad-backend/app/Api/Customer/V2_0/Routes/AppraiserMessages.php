<?php
namespace ValuePad\Api\Customer\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Customer\V2_0\Controllers\AppraiserMessagesController;

class AppraiserMessages implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get('customers/{customerId}/appraisers/{appraiserId}/messages', AppraiserMessagesController::class.'@index');
        $registrar->get('customers/{customerId}/appraisers/{appraiserId}/messages/total', AppraiserMessagesController::class.'@total');
    }
}
