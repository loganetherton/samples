<?php
namespace ValuePad\Api\Appraiser\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Appraiser\V2_0\Controllers\AppraisersController;

class Appraisers implements RouteRegistrarInterface
{
    /**
     * @param RegistrarInterface $registrar
     */
    public function register(RegistrarInterface $registrar)
    {
        $registrar->resource('appraisers', AppraisersController::class, [
            'except' => ['destroy']
        ]);

		$registrar->post(
			'appraisers/{appraiserId}/change-primary-license',
			AppraisersController::class.'@changePrimaryLicense'
		);


		$registrar->patch(
			'appraisers/{appraiserId}/availability',
			AppraisersController::class.'@updateAvailability'
		);
    }
}
