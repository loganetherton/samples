<?php
namespace ValuePad\Api\Support;

use Asm89\Stack\CorsService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use ValuePad\Support\AfterPartyMiddleware;
use ValuePad\Support\DefaultEnvironmentDetectorReplacerTrait;
use Exception;
use ValuePad\Support\RegisterLogglyViaBootstrapperTrait;

class Kernel extends HttpKernel
{
    use DefaultEnvironmentDetectorReplacerTrait;
	use RegisterLogglyViaBootstrapperTrait;

    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
		// It causes issues with CORS when responses with non 200 HTTP code
		
		//ClockworkMiddleware::class

        AccessMiddleware::class,
        AfterPartyMiddleware::class
	];

    protected $routeMiddleware = [
        'act-as-assignee' => ActAsAssigneeMiddleware::class
    ];


    /**
     * @param Application $app
     * @param Router $router
     */
    public function __construct(Application $app, Router $router)
    {
		$this->bootstrappers = $this->registerLogglyViaBootstrapper($this->bootstrappers);
        $this->bootstrappers = $this->replaceDefaultDetectEnvironmentBootstrapper($this->bootstrappers);

        parent::__construct($app, $router);
    }

	/**
	 * @param Request $request
	 * @param Exception $e
	 * @return Response
	 */
	protected function renderException($request, Exception $e)
	{
		$response = parent::renderException($request, $e);

		/**
		 * @var CorsService $corsService
		 */
		$corsService = $this->app->make(CorsService::class);

		return $corsService->addActualRequestHeaders($response, $request);
	}
}
