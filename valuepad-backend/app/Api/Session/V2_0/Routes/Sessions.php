<?php
namespace ValuePad\Api\Session\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Session\V2_0\Controllers\SessionsController;

/**
 *
 *
 */
class Sessions implements RouteRegistrarInterface
{

    /**
     *
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('sessions', SessionsController::class, [
            'only' => [
                'show',
                'store',
                'destroy'
            ]
        ]);

        $registrar->delete('sessions', SessionsController::class . '@destroyAll');
        $registrar->post('sessions/{sessionId}/refresh', SessionsController::class . '@refresh');
        $registrar->post('sessions/auto-login-tokens', SessionsController::class . '@storeAutoLoginToken');
    }
}
