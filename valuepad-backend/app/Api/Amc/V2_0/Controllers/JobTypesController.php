<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\JobTypesSearchableProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\JobTypeTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
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
     * @param int $amcId
     * @param int $customerId
     * @param JobTypesSearchableProcessor $processor
     * @return Response
     */
    public function index($amcId, $customerId, JobTypesSearchableProcessor $processor)
    {
        $options = new FetchJobTypesOptions();
        $options->setCriteria($processor->getCriteria());

        return $this->resource->makeAll(
            $this->jobTypeService->getAllVisible($customerId, $options),
            $this->transformer(JobTypeTransformer::class)
        );
    }

    /**
     * @param AmcService $amcService
     * @param CustomerService $customerService
     * @param int $amcId
     * @param int $customerId
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, CustomerService $customerService, $amcId, $customerId)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        return $customerService->isRelatedWithAmc($customerId, $amcId);
    }
}
