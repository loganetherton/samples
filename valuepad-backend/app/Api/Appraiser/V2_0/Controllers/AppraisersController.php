<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Illuminate\Http\Response;
use ValuePad\Api\Appraiser\V2_0\Processors\AppraisersProcessor;
use ValuePad\Api\Appraiser\V2_0\Processors\AppraisersSearchableProcessor;
use ValuePad\Api\Shared\Processors\AvailabilityProcessor;
use ValuePad\Api\Appraiser\V2_0\Transformers\AppraiserTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Appraiser\Exceptions\LicenseNotAllowedException;
use ValuePad\Core\Appraiser\Options\FetchAppraisersOptions;
use ValuePad\Core\Appraiser\Options\UpdateAppraiserOptions;
use ValuePad\Core\Appraiser\Persistables\AppraiserPersistable;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Asc\Services\AscService;
use ValuePad\Core\Asc\Entities\AscAppraiser;
use ValuePad\Core\Shared\Options\PaginationOptions;

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
     * @param AppraisersProcessor $processor
     * @return Response
     */
    public function store(AppraisersProcessor $processor)
    {
		$persistable = $processor->createPersistable();

		$secretLicense = $this->container->make('config')->get('app.secret_license');

		/**
		 * Here, we detect a request to generate a fake asc appraiser to facilitate appraiser sign-up process
		 */
		if ($secretLicense && object_take($persistable, 'qualifications.primaryLicense.number') === $secretLicense){

			$fakeAppraiser = $this->tryGenerateAscAppraiserBeforeCreateAppraiser($persistable);

			if ($fakeAppraiser !== null){
				$persistable->getQualifications()
					->getPrimaryLicense()
					->setNumber($fakeAppraiser->getLicenseNumber());
			}
		}

		try {
			$appraiser = $this->appraiserService->create($persistable);
		} catch (ErrorsThrowableCollection $errors){
			throw $this->adjustErrorsThrowableCollection($errors);
		}

        return $this->resource->make($appraiser, $this->transformer(AppraiserTransformer::class));
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
	 * @param AppraiserPersistable $persistable
	 * @return AscAppraiser
	 */
	private function tryGenerateAscAppraiserBeforeCreateAppraiser(AppraiserPersistable $persistable)
	{
		/**
		 * @var AscService $ascService
		 */
		$ascService = $this->container->make(AscService::class);

		$result = $this->appraiserService->dryCreateWithoutLicenseExistenceValidation($persistable);

		if ($result === false){
			return null;
		}

		return $ascService->generate($persistable->getQualifications()->getPrimaryLicense());
	}

	/**
	 * @param AppraisersSearchableProcessor $processor
	 * @return Response
	 */
	public function index(AppraisersSearchableProcessor $processor)
	{
		$adapter = new DefaultPaginatorAdapter([
			'getAll' => function($page, $perPage) use ($processor){
				$options = new FetchAppraisersOptions();
				$options->setPagination(new PaginationOptions($page, $perPage));
				$options->setCriteria($processor->getCriteria());
				return $this->appraiserService->getAll($options);
			},
			'getTotal' => function() use ($processor){
				return $this->appraiserService->getTotal($processor->getCriteria());
			}
		]);

		return $this->resource->makeAll($this->paginator($adapter), $this->transformer(AppraiserTransformer::class));
	}

    /**
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return $this->resource->make(
            $this->appraiserService->get($id),
            $this->transformer(AppraiserTransformer::class)
        );
    }

    /**
     * @param int $id
     * @param AppraisersProcessor $processor
	 * @throws ErrorsThrowableCollection
     * @return Response
     */
    public function update($id, AppraisersProcessor $processor)
    {
		$options = new UpdateAppraiserOptions();

		$options->setSoftValidationMode($processor->isSoftValidationMode());

        try {
			$this->appraiserService->update(
				$id,
				$processor->createPersistable(),
				$processor->schedulePropertiesToClear($options)
			);
		}catch (ErrorsThrowableCollection $errors){
			throw $this->adjustErrorsThrowableCollection($errors);
		}

        return $this->resource->blank();
    }

	/**
	 * @param int $appraiserId
	 * @param AvailabilityProcessor $processor
	 * @return Response
	 */
	public function updateAvailability($appraiserId, AvailabilityProcessor $processor)
	{
		$this->appraiserService->updateAvailability(
			$appraiserId,
			$processor->createPersistable(),
			$processor->schedulePropertiesToClear()
		);

		return $this->resource->blank();
	}

	/**
	 * @param ChangePrimaryLicenseProcessor $processor
	 * @param int $appraiserId
	 * @return Response
	 */
	public function changePrimaryLicense($appraiserId, ChangePrimaryLicenseProcessor $processor)
	{
		try {
			$this->appraiserService->changePrimaryLicense($appraiserId, $processor->getLicense());
		} catch (LicenseNotAllowedException $ex){
			$errors = new ErrorsThrowableCollection();

			$errors['license'] = new Error('permissions', $ex->getMessage());

			throw $errors;
		}

		return $this->resource->blank();
	}

    /**
     * @param int $id
     * @param AppraiserService $appraiserService
     * @return bool
     */
    public static function verifyAction($id, AppraiserService $appraiserService)
    {
        return $appraiserService->exists($id);
    }
}
