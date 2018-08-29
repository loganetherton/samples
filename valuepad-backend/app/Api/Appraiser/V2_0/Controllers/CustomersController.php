<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\CustomersSearchableProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\CustomerTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Customer\Options\FetchCustomerOptions;
use ValuePad\Core\Customer\Services\CustomerService;

class CustomersController extends BaseController
{
	/**
	 * @var CustomerService
	 */
	private $customerService;

	/**
	 * @param CustomerService $customerService
	 */
	public function initialize(CustomerService $customerService)
	{
		$this->customerService = $customerService;
	}

	/**
	 * @param int $appraiserId
	 * @param CustomersSearchableProcessor $processor
	 * @return Response
	 */
	public function index($appraiserId, CustomersSearchableProcessor $processor)
	{
		$options = new FetchCustomerOptions();

		$options->setSortables($processor->createSortables());

		return $this->resource->makeAll(
			$this->customerService->getAllByAppraiserId($appraiserId, $options),
			$this->transformer(CustomerTransformer::class)
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
