<?php
namespace ValuePad\Api\Appraiser\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Appraiser\V2_0\Controllers\OrdersController;

class Orders implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->get('appraisers/{appraiserId}/orders/accounting', OrdersController::class.'@accounting');

		$registrar->resource('appraisers.orders', OrdersController::class, ['only' => ['index', 'show']]);

		$registrar->post('appraisers/{appraiserId}/orders/{orderId}/accept', OrdersController::class.'@accept');
		$registrar->post('appraisers/{appraiserId}/orders/{orderId}/reassign', OrdersController::class.'@reassign');

		$registrar->post(
			'appraisers/{appraiserId}/orders/{orderId}/accept-with-conditions',
			OrdersController::class.'@acceptWithConditions'
		);

		$registrar->post('appraisers/{appraiserId}/orders/{orderId}/decline', OrdersController::class.'@decline');

		$registrar->post('appraisers/{appraiserId}/orders/{orderId}/pay-tech-fee', OrdersController::class.'@pay');

		$registrar->get('appraisers/{appraiserId}/orders/totals', OrdersController::class.'@totals');

		$registrar->post('appraisers/{appraiserId}/orders/{orderId}/change-additional-status',
			OrdersController::class.'@changeAdditionalStatus');

		$registrar->get('appraisers/{appraiserId}/orders/{orderId}/additional-statuses',
			OrdersController::class.'@listAdditionalStatuses');
	}
}
