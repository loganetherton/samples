<?php
namespace ValuePad\Api\Appraiser\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Appraiser\V2_0\Controllers\DocumentController;

class Document implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->get(
			'appraisers/{appraiserId}/orders/{orderId}/document/formats',
			DocumentController::class.'@formats'
		);

		$registrar->post(
			'appraisers/{appraiserId}/orders/{orderId}/document',
			DocumentController::class.'@store'
		);

		$registrar->get(
			'appraisers/{appraiserId}/orders/{orderId}/document',
			DocumentController::class.'@show'
		);

		$registrar->patch(
			'appraisers/{appraiserId}/orders/{orderId}/document',
			DocumentController::class.'@update'
		);

		$registrar->post(
			'appraisers/{appraiserId}/orders/{orderId}/document/email',
			DocumentController::class.'@email'
		);
	}
}
