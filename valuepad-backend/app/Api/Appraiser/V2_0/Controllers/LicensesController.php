<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Illuminate\Http\Response;
use ValuePad\Api\Appraiser\V2_0\Processors\LicensesProcessor;
use ValuePad\Api\Appraiser\V2_0\Transformers\LicenseTransformer;
use ValuePad\Api\Assignee\V2_0\Support\CoverageReformatter;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraiser\Options\UpdateLicenseOptions;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Appraiser\Services\LicenseService;
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
	 * @param int $appraiserId
	 * @param LicensesProcessor $processor
	 * @return Response
	 * @throws ErrorsThrowableCollection
	 */
	public function store($appraiserId, LicensesProcessor $processor)
	{
		try {
			$license = $this->licenseService->create($appraiserId, $processor->createPersistable());
		} catch (ErrorsThrowableCollection $ex){
			throw CoverageReformatter::reformatErrors($ex);
		}

		return $this->resource->make($license, $this->transformer(LicenseTransformer::class));
	}

	/**
	 * @param $appraiserId
	 * @param $licenseId
	 * @param LicensesProcessor $processor
	 * @return Response
	 * @throws ErrorsThrowableCollection
	 */
	public function update($appraiserId, $licenseId, LicensesProcessor $processor)
	{
		try {
			$this->licenseService->update(
				$licenseId,
				$processor->createPersistable(),
				$processor->schedulePropertiesToClear(new UpdateLicenseOptions())
			);
		} catch (ErrorsThrowableCollection $ex){
			throw CoverageReformatter::reformatErrors($ex);
		}

		return $this->resource->blank();
	}

	/**
	 * @param int $appraiserId
	 * @return Response
	 */
	public function index($appraiserId)
	{
		return $this->resource->makeAll(
			$this->licenseService->getAll($appraiserId),
			$this->transformer(LicenseTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @param int $licenseId
	 * @return Response
	 */
	public function show($appraiserId, $licenseId)
	{
		return $this->resource->make(
			$this->licenseService->get($licenseId),
			$this->transformer(LicenseTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @param int $licenseId
	 * @return Response
	 */
	public function destroy($appraiserId, $licenseId)
	{
		$this->licenseService->delete($licenseId);

		return $this->resource->blank();
	}

	/**
	 * @param AppraiserService $appraiserService
	 * @param int $appraiserId
	 * @param int $licenseId
	 * @return bool
	 */
	public static function verifyAction(AppraiserService $appraiserService, $appraiserId, $licenseId = null)
	{
		if (!$appraiserService->exists($appraiserId)){
			return false;
		}

		if ($licenseId === null){
			return true;
		}

		return $appraiserService->hasLicense($appraiserId, $licenseId);
	}
}
