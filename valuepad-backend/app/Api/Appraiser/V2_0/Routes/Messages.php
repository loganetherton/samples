<?php
namespace ValuePad\Api\Appraiser\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Appraiser\V2_0\Controllers\MessagesController;

class Messages implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->get(
			'appraisers/{appraiserId}/orders/{orderId}/messages',
			MessagesController::class.'@indexByOrder'
		);

		$registrar->post(
			'appraisers/{appraiserId}/orders/{orderId}/messages',
			MessagesController::class.'@store'
		);

		$registrar->get(
			'appraisers/{appraiserId}/messages',
			MessagesController::class.'@index'
		);

		$registrar->get(
			'appraisers/{appraiserId}/messages/{messageId}',
			MessagesController::class.'@show'
		);

		$registrar->post(
			'appraisers/{appraiserId}/messages/{messageId}/mark-as-read',
			MessagesController::class.'@markAsRead'
		);

		$registrar->post(
			'appraisers/{appraiserId}/messages/mark-all-as-read',
			MessagesController::class.'@markAllAsRead'
		);

		$registrar->post(
			'appraisers/{appraiserId}/messages/mark-as-read',
			MessagesController::class.'@markSomeAsRead'
		);

		$registrar->get(
			'appraisers/{appraiserId}/messages/total',
			MessagesController::class.'@total'
		);
	}
}
