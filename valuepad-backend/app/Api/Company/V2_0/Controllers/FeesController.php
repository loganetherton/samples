<?php
namespace ValuePad\Api\Company\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Company\V2_0\Processors\FeeProcessor;
use ValuePad\Api\Company\V2_0\Processors\FeesProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Services\FeeService;

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
     * @param int $companyId
     * @return Response
     */
    public function index($companyId)
    {
        return $this->resource->makeAll($this->feeService->getAll($companyId), $this->transformer());
    }

    /**
     * @param int $companyId
     * @param FeeProcessor $processor
     * @return Response
     */
    public function store($companyId, FeeProcessor $processor)
    {
        return $this->resource->make(
            $this->feeService->create($companyId, $processor->createPersistable()),
            $this->transformer()
        );
    }

    /**
     * @param int $companyId
     * @param FeesProcessor $processor
     * @return Response
     */
    public function replace($companyId, FeesProcessor $processor)
    {
        $this->feeService->sync($companyId, $processor->createPersistables());

        return $this->resource->blank();
    }

    /**
     * @param CompanyService $companyService
     * @param int $companyId
     * @return bool
     */
    public static function verifyAction(CompanyService $companyService, $companyId)
    {
        return $companyService->exists($companyId);
    }
}
