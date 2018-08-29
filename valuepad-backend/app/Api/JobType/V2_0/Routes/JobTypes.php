<?php
namespace ValuePad\Api\JobType\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\JobType\V2_0\Controllers\JobTypesController;

class JobTypes implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->resource('job-types', JobTypesController::class, [
			'only' => ['index']
		]);
	}
}
