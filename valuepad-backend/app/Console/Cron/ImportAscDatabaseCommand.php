<?php
namespace ValuePad\Console\Cron;

use ValuePad\Core\Asc\Services\AscService;

class ImportAscDatabaseCommand extends AbstractCommand
{
	/**
	 * @param AscService $ascService
	 */
	public function fire(AscService $ascService)
	{
		$ascService->import();
	}
}
