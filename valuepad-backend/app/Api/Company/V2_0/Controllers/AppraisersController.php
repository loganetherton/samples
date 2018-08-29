<?php
namespace ValuePad\Api\Company\V2_0\Controllers;

use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Illuminate\Http\Response;
use ValuePad\Api\Appraiser\V2_0\Processors\AppraisersProcessor;
use ValuePad\Api\Appraiser\V2_0\Transformers\AppraiserTransformer;
use ValuePad\Api\Company\V2_0\Processors\AppraisersSearchableProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraiser\Options\UpdateAppraiserOptions;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Company\Services\CompanyService;

class AppraisersController extends BaseController
{
    /**
     * @var AppraiserService
     */
    private $appraiserService;

    /**
     * @param AppraiserService $appraiserService
     */
    public function initialize(AppraiserService $appraiserService)
    {
        $this->appraiserService = $appraiserService;
    }

    /**
     * @param int $companyId
     * @param AppraisersSearchableProcessor $processor
     * @return Response
     */
    public function index($companyId, AppraisersSearchableProcessor $processor)
    {
        return $this->resource->makeAll(
            $this->appraiserService->getAllByCompanyId($companyId, $processor->getOrderId(), $processor->getDistance()),
            $this->transformer(AppraiserTransformer::class)
        );
    }

    /**
     * @param int $companyId
     * @param int $appraiserId
     * @return Response
     */
    public function show($companyId, $appraiserId)
    {
        return $this->resource->make(
            $this->appraiserService->get($appraiserId),
            $this->transformer(AppraiserTransformer::class)
        );
    }

    /**
     * @param AppraisersProcessor $processor
     * @param int $companyId
     * @param int $appraiserId
     * @return Response
     */
    public function update(AppraisersProcessor $processor, $companyId, $appraiserId)
    {
        $options = new UpdateAppraiserOptions();

        $options->setSoftValidationMode($processor->isSoftValidationMode());

        try {
            $this->appraiserService->update(
                $appraiserId,
                $processor->createPersistable(),
                $processor->schedulePropertiesToClear($options)
            );
        } catch (ErrorsThrowableCollection $errors) {
            throw $this->adjustErrorsThrowableCollection($errors);
        }

        return $this->resource->blank();
    }

    /**
     * @param ErrorsThrowableCollection $errors
     * @return ErrorsThrowableCollection
     */
    private function adjustErrorsThrowableCollection(ErrorsThrowableCollection $errors)
    {
        $namespace = 'qualifications.primaryLicense.';

        if (isset($errors[$namespace.'coverages'])){
            $errors[$namespace.'coverage'] = $errors[$namespace.'coverages'];
            unset($errors[$namespace.'coverages']);
        }

        return $errors;
    }

    /**
     * @param CompanyService $companyService
     * @param AppraiserService $appraiserService
     * @param int $companyId
     * @param int $appraiserId
     * @return bool
     */
    public static function verifyAction(
        CompanyService $companyService,
        AppraiserService $appraiserService,
        $companyId,
        $appraiserId = null
    ) {
        if (! $companyService->exists($companyId)) {
            return false;
        }

        if ($appraiserId) {
            if (! $appraiserService->exists($appraiserId)) {
                return false;
            }

            if (! $companyService->hasStaffAsUser($companyId, $appraiserId)) {
                return false;
            }
        }

        return true;
    }
}
