<?php
namespace ValuePad\Console\Artisan;

use Illuminate\Foundation\Providers\ArtisanServiceProvider;

class ServiceProvider extends ArtisanServiceProvider
{
	protected function registerOptimizeCommand()
	{
		$this->app->singleton('command.optimize', function ($app) {
			return new FixedOptimizeCommand($app['composer']);
		});
	}
}
