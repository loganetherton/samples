<?php
namespace ValuePad\Api\Appraiser\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Appraiser\V2_0\Controllers\DefaultFeesController;
use ValuePad\Api\Appraiser\V2_0\Controllers\CustomerFeesController;
use ValuePad\Api\Appraiser\V2_0\Controllers\FeesController;

class Fees implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->resource('appraisers.customers.fees', CustomerFeesController::class, [
			'except' => 'shows'
		]);

		$registrar->patch('appraisers/{appraiserId}/customers/{customerId}/fees',
			CustomerFeesController::class.'@updateBulk');

		$registrar->delete('appraisers/{appraiserId}/customers/{customerId}/fees',
			CustomerFeesController::class.'@destroyBulk');

		$registrar->resource('appraisers.fees', DefaultFeesController::class, [
			'except' => 'shows'
		]);

		$registrar->patch('appraisers/{appraiserId}/fees', DefaultFeesController::class.'@updateBulk');
		$registrar->delete('appraisers/{appraiserId}/fees', DefaultFeesController::class.'@destroyBulk');

		$registrar->get('appraisers/{appraiserId}/fees/totals', FeesController::class.'@totals');
	}
}
