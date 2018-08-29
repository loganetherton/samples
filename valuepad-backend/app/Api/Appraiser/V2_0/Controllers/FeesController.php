<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Assignee\V2_0\Transformers\TotalTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Appraiser\Services\FeeService;

class FeesController extends BaseController
{
	/**
	 * @var FeeService
	 */
	private $feeService;

	/**
	 * @param FeeService $feeService
	 */
	public function initialize(FeeService $feeService)
	{
		$this->feeService = $feeService;
	}

	/**
	 * @param $appraiserId
	 * @return Response
	 */
	public function totals($appraiserId)
	{
		return $this->resource->makeAll(
			$this->feeService->getTotals($appraiserId),
			$this->transformer(TotalTransformer::class)
		);
	}

	/**
	 * @param AppraiserService $appraiserService
	 * @param int $appraiserId
	 * @return bool
	 */
	public static function verifyAction(AppraiserService $appraiserService, $appraiserId)
	{
		return $appraiserService->exists($appraiserId);
	}
}
