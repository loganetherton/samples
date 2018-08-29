<?php
namespace ValuePad\Api\Customer\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Customer\V2_0\Controllers\WorkflowController;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;

class Workflow implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$values = ProcessStatus::toArray();

		foreach ($values as $value){
			$registrar->post(
				'customers/{customerId}/orders/{ordersId}/workflow/'.$value,
				WorkflowController::class.'@'.camel_case($value === 'new' ? 'fresh' : $value)
			);
		}
	}
}
