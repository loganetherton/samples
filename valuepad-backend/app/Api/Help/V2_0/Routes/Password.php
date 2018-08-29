<?php
namespace ValuePad\Api\Help\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Help\V2_0\Controllers\PasswordController;

class Password implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->post('password/reset', PasswordController::class.'@reset');
		$registrar->post('password/change', PasswordController::class.'@change');
	}
}
