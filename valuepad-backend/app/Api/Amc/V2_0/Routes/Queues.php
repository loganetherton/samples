<?php
namespace ValuePad\Api\Amc\V2_0\Routes;
use Ascope\Libraries\Routing\Router;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Amc\V2_0\Controllers\QueuesController;
use ValuePad\Core\Appraisal\Enums\Queue;

class Queues implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface|Router $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar
            ->get('amcs/{amcId}/queues/{name}', QueuesController::class.'@index')
            ->where('name', '('.implode('|', Queue::toArray()).')');

        $registrar->get('amcs/{amcId}/queues/counters',  QueuesController::class.'@counters');
    }
}
