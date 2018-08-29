<?php
namespace ValuePad\Api\Appraiser\V2_0\Routes;

use Ascope\Libraries\Routing\RouteRegistrarInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarInterface;
use ValuePad\Api\Appraiser\V2_0\Controllers\AdditionalDocumentsController;

class AdditionalDocuments implements RouteRegistrarInterface
{
	/**
	 * @param RegistrarInterface $registrar
	 */
	public function register(RegistrarInterface $registrar)
	{
		$registrar->get(
			'appraisers/{appraiserId}/orders/{orderId}/additional-documents/types',
			AdditionalDocumentsController::class.'@types'
		);

		$registrar->resource(
			'appraisers.orders.additional-documents',
			AdditionalDocumentsController::class, ['only' => ['store', 'index']]
		);

		$registrar->post(
			'appraisers/{appraiserId}/orders/{orderId}/additional-documents/{additionalDocumentId}/email',
			AdditionalDocumentsController::class.'@email'
		);
	}
}
