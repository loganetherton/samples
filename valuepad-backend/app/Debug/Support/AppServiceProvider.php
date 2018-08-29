<?php
namespace ValuePad\Debug\Support;

use Illuminate\Mail\Mailer;
use Illuminate\Support\ServiceProvider;
use ValuePad\Core\Appraisal\Interfaces\ExtractorInterface;
use ValuePad\Core\Payment\Interfaces\PaymentSystemInterface;
use ValuePad\Live\Support\PusherWrapperInterface;
use Swift_Mailer;
use ValuePad\Mobile\Support\Notifier as MobileNotifier;
use ValuePad\Debug\Support\MobileNotifier as DebugMobileNotifier;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * @param Mailer $mailer
	 */
	public function boot(Mailer $mailer)
	{
		if ($this->app->environment() === 'tests'){
			$mailer->setSwiftMailer(new Swift_Mailer(new EmailTransport($this->app)));
		}
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		if ($this->app->environment() === 'tests'){
			$this->app->bind(PusherWrapperInterface::class, function(){
				return new PusherWrapper($this->app);
			});

			$this->app->singleton(ExtractorInterface::class, function(){
				return new Extractor();
			});

			$this->app->singleton(PaymentSystemInterface::class, function(){
				return new PaymentSystem();
			});

			$this->app->bind(MobileNotifier::class, function(){
				return new DebugMobileNotifier($this->app);
			});
		}
	}
}
