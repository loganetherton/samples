<?php
namespace ValuePad\Api\Customer\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Customer\V2_0\Controllers\InvitationsController;

class Invitations implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->resource('customers.invitations', InvitationsController::class, [
			'only' => ['index', 'store', 'show']
		]);
	}
}
