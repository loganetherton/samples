<?php
namespace ValuePad\Api\Appraiser\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Appraiser\V2_0\Controllers\CreditCardController;

class CreditCard implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->put('appraisers/{appraiserId}/payment/credit-card', CreditCardController::class.'@replace');
		$registrar->get('appraisers/{appraiserId}/payment/credit-card', CreditCardController::class.'@show');
	}
}
