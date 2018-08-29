<?php
namespace ValuePad\Api\Customer\V2_0\Routes;
use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Customer\V2_0\Controllers\AppraiserQueuesController;
use ValuePad\Core\Appraisal\Enums\Queue;

class AppraiserQueues implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->get(
            'customers/{customerId}/appraisers/{appraiserId}/queues/{name}',
            AppraiserQueuesController::class.'@index'
        )->where('name', '('.implode('|', Queue::toArray()).')');


        $registrar->get(
            'customers/{customerId}/appraisers/{appraiserId}/queues/counters',
            AppraiserQueuesController::class.'@counters'
        );
    }
}
