<?php
namespace ValuePad\Live\Hook;

use Illuminate\Routing\Router;
use ValuePad\Support\Shortcut;

class Routes
{
	/**
	 * @param Router $router
	 */
	public function register(Router $router)
	{
		$router->group(['middleware' => 'cors'], function(Router $router){
			$router->post(Shortcut::prependGlobalRoutePrefix('live/auth'), Controller::class.'@auth');
		});
	}
}
