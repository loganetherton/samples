<?php
namespace ValuePad\Live\Support;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(PusherWrapperInterface::class, function(){
			$config = $this->app->make('config')->get('pusher');

			$key = $config['key'];
			$secret = $config['secret'];
			$appId = $config['app_id'];

			unset($config['key'], $config['secret'], $config['app_id']);

			return new PusherWrapper($key, $secret, $appId, $config);
		});
	}
}
