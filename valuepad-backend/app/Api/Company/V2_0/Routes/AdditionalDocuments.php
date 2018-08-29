<?php
namespace ValuePad\Api\Company\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\AdditionalDocumentsController;

class AdditionalDocuments implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get(
            'managers/{managerId}/orders/{orderId}/additional-documents/types',
            AdditionalDocumentsController::class.'@types'
        );

        $registrar->resource(
            'managers.orders.additional-documents',
            AdditionalDocumentsController::class, ['only' => ['store', 'index']]
        );
    }
}
