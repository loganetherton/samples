<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Illuminate\Http\Response;
use ValuePad\Api\Amc\V2_0\Processors\LicensesProcessor;
use ValuePad\Api\Amc\V2_0\Transformers\LicenseTransformer;
use ValuePad\Api\Assignee\V2_0\Support\CoverageReformatter;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Services\LicenseService;

class LicensesController extends BaseController
{
    /**
     * @var LicenseService
     */
    private $licenseService;

    /**
     * @param LicenseService $licenseService
     */
    public function initialize(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * @param int $amcId
     * @return Response
     */
    public function index($amcId)
    {
        return $this->resource->makeAll(
            $this->licenseService->getAll($amcId),
            $this->transformer(LicenseTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $licenseId
     * @return Response
     */
    public function show($amcId, $licenseId)
    {
        return $this->resource->make(
            $this->licenseService->get($licenseId),
            $this->transformer(LicenseTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param LicensesProcessor $processor
     * @return Response
     */
    public function store($amcId, LicensesProcessor $processor)
    {
        try {
            $license = $this->licenseService->create($amcId, $processor->createPersistable());
        } catch (ErrorsThrowableCollection $ex){
            throw CoverageReformatter::reformatErrors($ex);
        }

        return $this->resource->make($license, $this->transformer(LicenseTransformer::class));
    }

    /**
     * @param int $amcId
     * @param int $licenseId
     * @param LicensesProcessor $processor
     * @return Response
     */
    public function update($amcId, $licenseId, LicensesProcessor $processor)
    {
        try {
            $this->licenseService->update(
                $licenseId,
                $processor->createPersistable(),
                $processor->schedulePropertiesToClear()
            );
        } catch (ErrorsThrowableCollection $ex){
            throw CoverageReformatter::reformatErrors($ex);
        }

        return $this->resource->blank();
    }

    /**
     * @param string $amcId
     * @param string $licenseId
     * @return Response
     */
    public function destroy($amcId, $licenseId)
    {
        $this->licenseService->delete($licenseId);

        return $this->resource->blank();
    }

    /**
     * @param AmcService $amcService
     * @param int $amcId
     * @param int $licenseId
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, $amcId, $licenseId = null)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        if ($licenseId === null){
            return true;
        }

        return $amcService->hasLicense($amcId, $licenseId);
    }
}
