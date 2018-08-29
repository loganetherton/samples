<?php
namespace ValuePad\Console\Cron;

use ValuePad\Core\Appraiser\Services\AppraiserService;

class MarkReturnedAppraisersAsAvailableCommand extends AbstractCommand
{
	/**
	 * @param AppraiserService $appraiserService
	 */
	public function fire(AppraiserService $appraiserService)
	{
		$appraiserService->markAllReturnedAsAvailable();
	}
}
