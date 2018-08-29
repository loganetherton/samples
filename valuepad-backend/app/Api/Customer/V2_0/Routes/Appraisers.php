<?php
namespace ValuePad\Api\Customer\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Customer\V2_0\Controllers\AppraisersController;

class Appraisers implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->get('customers/{customerId}/appraisers/{appraiserId}/logs', AppraisersController::class.'@logs');
		$registrar->get('customers/{customerId}/appraisers', AppraisersController::class.'@index');
		$registrar->get('customers/{customerId}/appraisers/{appraiserId}', AppraisersController::class.'@show');
		$registrar->get('customers/{customerId}/appraisers/{appraiserId}/ach', AppraisersController::class.'@ach');
		$registrar->get('customers/{customerId}/appraisers/{appraiserId}/settings', AppraisersController::class.'@settings');
	}
}
