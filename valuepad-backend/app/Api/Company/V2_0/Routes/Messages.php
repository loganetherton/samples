<?php
namespace ValuePad\Api\Company\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\MessagesController;

class Messages implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get(
            'managers/{managerId}/orders/{orderId}/messages',
            MessagesController::class.'@indexByOrder'
        );

        $registrar->post(
            'managers/{managerId}/orders/{orderId}/messages',
            MessagesController::class.'@store'
        );

        $registrar->get(
            'managers/{managerId}/messages',
            MessagesController::class.'@index'
        );

        $registrar->get(
            'managers/{managerId}/messages/{messageId}',
            MessagesController::class.'@show'
        );

        $registrar->post(
            'managers/{managerId}/messages/{messageId}/mark-as-read',
            MessagesController::class.'@markAsRead'
        );

        $registrar->post(
            'managers/{managerId}/messages/mark-all-as-read',
            MessagesController::class.'@markAllAsRead'
        );

        $registrar->post(
            'managers/{managerId}/messages/mark-as-read',
            MessagesController::class.'@markSomeAsRead'
        );

        $registrar->get(
            'managers/{managerId}/messages/total',
            MessagesController::class.'@total'
        );
    }
}
