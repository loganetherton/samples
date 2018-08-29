<?php
namespace ValuePad\Api\JobType\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\JobType\V2_0\Transformers\JobTypeTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\JobType\Services\JobTypeService;

class JobTypesController extends BaseController
{
	/**
	 * @var JobTypeService
	 */
	private $jobTypeService;

	/**
	 * @param JobTypeService $jobTypeService
	 */
	public function initialize(JobTypeService $jobTypeService)
	{
		$this->jobTypeService = $jobTypeService;
	}

	/**
	 * @return Response
	 */
	public function index()
	{
		return $this->resource->makeAll(
			$this->jobTypeService->getAll(),
			$this->transformer(JobTypeTransformer::class)
		);
	}
}
