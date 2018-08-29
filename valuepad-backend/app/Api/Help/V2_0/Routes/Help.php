<?php
namespace ValuePad\Api\Help\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Help\V2_0\Controllers\HelpController;

class Help implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->post('help/issues', HelpController::class.'@storeIssues');
		$registrar->post('help/feature-requests', HelpController::class.'@storeFeatureRequests');
		$registrar->post('help/hints', HelpController::class.'@hints');
	}
}
