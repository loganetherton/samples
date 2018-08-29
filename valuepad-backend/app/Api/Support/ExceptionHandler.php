<?php
namespace ValuePad\Api\Support;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionHandler extends Handler
{

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class
    ];

    /**
     *
     * @param Exception $e
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $e
     * @return Response
     */
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }
}
