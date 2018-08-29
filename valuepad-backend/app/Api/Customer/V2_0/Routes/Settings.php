<?php
namespace ValuePad\Api\Customer\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Customer\V2_0\Controllers\SettingsController;

class Settings implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->patch('customers/{customerId}/settings', SettingsController::class.'@update');
		$registrar->get('customers/{customerId}/settings', SettingsController::class.'@show');
	}
}
