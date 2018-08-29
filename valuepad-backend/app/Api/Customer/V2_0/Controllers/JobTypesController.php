<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\JobTypesSearchableProcessor;
use ValuePad\Api\Customer\V2_0\Processors\JobTypesProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\JobTypeTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Customer\Options\FetchJobTypesOptions;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Customer\Services\JobTypeService;

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
	 * @param int $customerId
     * @param JobTypesSearchableProcessor $processor
	 * @return Response
	 */
	public function index($customerId, JobTypesSearchableProcessor $processor)
	{
        $options = new FetchJobTypesOptions();
        $options->setCriteria($processor->getCriteria());

		return $this->resource->makeAll(
			$this->jobTypeService->getAllVisible($customerId, $options),
			$this->transformer(JobTypeTransformer::class)
		);
	}

	/**
	 * @param $customerId
	 * @param JobTypesProcessor $processor
	 * @return Response
	 */
	public function store($customerId, JobTypesProcessor $processor)
	{
		return $this->resource->make(
			$this->jobTypeService->create($customerId, $processor->createPersistable()),
			$this->transformer(JobTypeTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $jobTypeId
	 * @param JobTypesProcessor $processor
	 * @return Response
	 */
	public function update($customerId, $jobTypeId, JobTypesProcessor $processor)
	{
		$this->jobTypeService->update(
			$jobTypeId,
			$processor->createPersistable(),
			$processor->schedulePropertiesToClear()
		);

		return $this->resource->blank();
	}

	/**
	 * @param int $customerId
	 * @param int $jobTypeId
	 * @return Response
	 */
	public function destroy($customerId, $jobTypeId)
	{
		$this->jobTypeService->delete($jobTypeId);
		return $this->resource->blank();
	}

	/**
	 * @param CustomerService $customerService
	 * @param int $customerId
	 * @param int $jobTypeId
	 * @return bool
	 */
	public static function verifyAction(CustomerService $customerService, $customerId, $jobTypeId = null)
	{
		if (!$customerService->exists($customerId)){
			return false;
		}

		if ($jobTypeId === null){
			return true;
		}

		return $customerService->hasVisibleJobType($customerId, $jobTypeId);
	}
}
