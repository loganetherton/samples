<?php
namespace ValuePad\Api\Location\V2_0\Routes;

use Ascope\Libraries\Routing\Router;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Location\V2_0\Controllers\CountiesController;

class Counties implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface|Router $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->get(
			'location/states/{stateCode}/counties',
			CountiesController::class.'@index'
		)->where('stateCode', '...state');


		$registrar->get(
			'location/states/{stateCode}/counties/{county}',
			CountiesController::class.'@show'
		)->where('stateCode', '...state');
	}
}
