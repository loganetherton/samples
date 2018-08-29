<?php
namespace ValuePad\Api\Appraiser\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Ascope\Libraries\Routing\Router;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Appraiser\V2_0\Controllers\QueuesController;
use ValuePad\Core\Appraisal\Enums\Queue;

class Queues implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface|Router $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->get(
			'appraisers/{appraiserId}/queues/{name}',
			QueuesController::class.'@index'
		)->where('name', '('.implode('|', Queue::toArray()).')');


		$registrar->get(
			'appraisers/{appraiserId}/queues/counters',
			QueuesController::class.'@counters'
		);
	}
}
