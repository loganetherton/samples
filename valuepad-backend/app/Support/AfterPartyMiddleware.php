<?php
namespace ValuePad\Support;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The middleware is used to run the code after the response is sent to the client
 *
 *
 */
class AfterPartyMiddleware
{
    /**
     * @var callable[]
     */
    private $callbacks = [];

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate()
    {
        foreach ($this->callbacks as $callback){
            $callback();
        }
    }

    /**
     * Schedule the callback to be executed after the response is sent to the client
     *
     * @param callable $callback
     * @return $this
     */
    public function schedule(callable $callback)
    {
        $this->callbacks[] = $callback;

        return $this;
    }
}
