<?php
namespace ValuePad\Api\User\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\User\V2_0\Controllers\DevicesController;

class Devices implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->delete('users/{id}/devices/{deviceId}', DevicesController::class.'@destroy');
		$registrar->post('users/{id}/devices', DevicesController::class.'@store');
	}
}
