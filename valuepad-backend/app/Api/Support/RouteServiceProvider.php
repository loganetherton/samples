<?php
namespace ValuePad\Api\Support;

use Ascope\Libraries\Routing\Router;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router as IlluminateRouter;
use ValuePad\Api\Shared\Controllers\DefaultController;
use ValuePad\Support\Shortcut;

class RouteServiceProvider extends ServiceProvider
{
	/**
	 * @param Router $router
	 */
    protected function define(Router $router)
    {
		$router->get(Shortcut::prependGlobalRoutePrefix('/'), DefaultController::class.'@server');

        $router->group([
            'prefix' => Shortcut::prependGlobalRoutePrefix('v2.0'),
            'middleware' => ['cors', 'act-as-assignee']
        ], function (Router $router) {

			$router->get('/', DefaultController::class.'@api');

            $packages = $this->app->make('config')
                ->get('app.packages');

            foreach ($packages as $package) {
                $path = app_path('Api/' . str_replace('\\', '/', $package) . '/V2_0/Routes');
                $namespace = 'ValuePad\Api\\' . $package . '\V2_0\Routes';

                if (! file_exists($path)) {
                    continue;
                }

                $finder = new Finder();

                /**
                 * @var SplFileInfo[] $files
                 */
                $files = $finder->in($path)
                    ->files()
                    ->name('*.php');

                foreach ($files as $file) {
                    $name = cut_string_right($file->getFilename(), '.php');
                    $this->app->make($namespace . '\\' . $name)
                        ->register($router);
                }
            }
        });
    }

    /**
     * @param IlluminateRouter $router
     */
    public function map(IlluminateRouter $router)
    {
        /**
         * @var Router $proxy
         */
        $proxy = $this->app->make(Router::class);

        $this->define($proxy);

        $routes = $router->getRoutes();

        foreach ($proxy->getRoutes() as $route) {
            $routes->add($route);
        }

        $router->setRoutes($routes);
    }
}
