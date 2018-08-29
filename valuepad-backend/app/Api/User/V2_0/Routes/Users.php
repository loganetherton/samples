<?php
namespace ValuePad\Api\User\V2_0\Routes;

use Ascope\Libraries\Routing\Router;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\User\V2_0\Controllers\UsersController;
use ValuePad\Core\User\Validation\Rules\Username;

class Users implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface|Router $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar
			->get('users/{username}', UsersController::class.'@show')
			->where('username', Username::ALLOWED_CHARACTERS);
	}
}
