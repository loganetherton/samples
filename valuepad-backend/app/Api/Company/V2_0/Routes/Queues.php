<?php
namespace ValuePad\Api\Company\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Company\V2_0\Controllers\QueuesController;
use ValuePad\Core\Appraisal\Enums\Queue;

class Queues implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get(
            'managers/{managerId}/queues/{name}',
            QueuesController::class.'@index'
        )->where('name', '('.implode('|', Queue::toArray()).')');


        $registrar->get(
            'managers/{managerId}/queues/counters',
            QueuesController::class.'@counters'
        );
    }
}
