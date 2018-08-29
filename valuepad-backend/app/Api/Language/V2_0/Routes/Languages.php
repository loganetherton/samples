<?php
namespace ValuePad\Api\Language\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Language\V2_0\Controllers\LanguagesController;

/**
 *
 *
 */
class Languages implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('languages', LanguagesController::class, [
            'only' => 'index'
        ]);
    }
}
