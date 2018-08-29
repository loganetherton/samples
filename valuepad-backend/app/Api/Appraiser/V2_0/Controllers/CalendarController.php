<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Appraiser\V2_0\Processors\CalendarSearchableProcessor;
use ValuePad\Api\Appraiser\V2_0\Transformers\BadgeTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Services\CalendarService;
use ValuePad\Core\Appraiser\Services\AppraiserService;

class CalendarController extends BaseController
{
	/**
	 * @var CalendarService
	 */
	private $calendarService;

	/**
	 * @param CalendarService $calendarService
	 */
	public function initialize(CalendarService $calendarService)
	{
		$this->calendarService = $calendarService;
	}

	/**
	 * @param int $appraiserId
	 * @param CalendarSearchableProcessor $processor
	 * @return Response
	 */
	public function day($appraiserId, CalendarSearchableProcessor $processor)
	{
		return $this->resource->makeAll(
			$this->calendarService->getAllBadgesWithDayScale($appraiserId, $processor->getFrom(), $processor->getTo()),
			$this->transformer(BadgeTransformer::class)
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
