<?php
namespace ValuePad\Console\Cron;

use ValuePad\Core\Session\Services\SessionService;

class DestroyExpiredSessionsCommand extends AbstractCommand
{
	/**
	 * @param SessionService $sessionService
	 */
	public function fire(SessionService $sessionService)
	{
		$sessionService->deleteAllExpired();
	}
}
