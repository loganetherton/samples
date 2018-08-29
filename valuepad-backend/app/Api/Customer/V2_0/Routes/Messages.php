<?php
namespace ValuePad\Api\Customer\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Customer\V2_0\Controllers\MessagesController;

class Messages implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->post('customers/{customerId}/orders/{orderId}/messages', MessagesController::class.'@store');
		$registrar->get('customers/{customerId}/orders/{orderId}/messages', MessagesController::class.'@indexByOrder');

		$registrar->post('customers/{customerId}/messages/mark-all-as-read', MessagesController::class.'@markAllAsRead');
		$registrar->post('customers/{customerId}/messages/mark-as-read', MessagesController::class.'@markSomeAsRead');
		$registrar->post('customers/{customerId}/messages/{messageId}/mark-as-read', MessagesController::class.'@markAsRead');


		$registrar->delete('customers/{customerId}/messages/{messageId}', MessagesController::class.'@destroy');
		$registrar->delete('customers/{customerId}/messages', MessagesController::class.'@destroyAll');

		$registrar->get('customers/{customerId}/messages', MessagesController::class.'@index');
		$registrar->get('customers/{customerId}/messages/{messagedId}', MessagesController::class.'@show');
	}
}
