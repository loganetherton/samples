<?php
namespace ValuePad\Api\Support;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use ValuePad\Api\Support\Converter\Populator\PopulatorFactory;
use Ascope\Libraries\Processor\PopulatorFactoryInterface;
use Ascope\Libraries\Processor\SharedModifiers as ProcessorModifiers;
use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Transformer\AbstractTransformer;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Core\Session\Services\SessionService;

/**
 *
 *
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(PopulatorFactoryInterface::class, PopulatorFactory::class);

        /**
         * Registers shared modifiers for all processors
         */
        AbstractProcessor::setSharedModifiersProvider(new ProcessorModifiers());

        /**
         * Registers shared modifiers for all transformers
         */
        AbstractTransformer::setSharedModifiersProvider(new TransformerModifiers($this->app));
    }

    public function register()
    {
		$this->app->singleton(Session::class, function(){
			/**
			 * @var Request $request
			 */
			$request = $this->app->make('request');

			$token = $request->header('Token');

			if (!$token) {
				return new Session();
			}

			/**
			 * @var SessionService $sessionService
			 */
			$sessionService = $this->app->make(SessionService::class);

			$session = $sessionService->getByToken($token);

			if (!$session){
				return new Session();
			}

			return $session;
		});
	}
}
