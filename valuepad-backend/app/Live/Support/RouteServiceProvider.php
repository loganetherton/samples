<?php
namespace ValuePad\Live\Support;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use ValuePad\Live\Hook\Routes;

class RouteServiceProvider extends ServiceProvider
{
	/**
	 * @param Router $router
	 */
	public function map(Router $router)
	{
		(new Routes())->register($router);
	}
}
