<?php
namespace ValuePad\Api\Document\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Document\V2_0\Controllers\DocumentsController;

class Documents implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
		$registrar->post('/documents', DocumentsController::class.'@store');
		$registrar->post('/documents/external', DocumentsController::class.'@storeExternal');
    }
}
