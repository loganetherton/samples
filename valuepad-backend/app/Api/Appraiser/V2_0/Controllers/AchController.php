<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Appraiser\V2_0\Processors\AchProcessor;
use ValuePad\Api\Appraiser\V2_0\Transformers\AchTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraiser\Services\AchService;
use ValuePad\Core\Appraiser\Services\AppraiserService;

class AchController extends BaseController
{
	/**
	 * @var AchService
	 */
	private $achService;

	/**
	 * @param AchService $achService
	 */
	public function initialize(AchService $achService)
	{
		$this->achService = $achService;
	}

	/**
	 * @param int $appraiserId
	 * @return Response
	 */
	public function show($appraiserId)
	{
		return $this->resource->make(
			$this->achService->getExistingOrEmpty($appraiserId),
			$this->transformer(AchTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @param AchProcessor $processor
	 * @return Response
	 */
	public function replace($appraiserId, AchProcessor $processor)
	{
		return $this->resource->make(
			$this->achService->replace($appraiserId, $processor->createPersistable()),
			$this->transformer(AchTransformer::class)
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
