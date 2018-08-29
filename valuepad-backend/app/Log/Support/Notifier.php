<?php
namespace ValuePad\Log\Support;

use ValuePad\Core\Log\Services\LogService;
use ValuePad\Core\Session\Services\SessionService;
use ValuePad\Core\Shared\Interfaces\NotifierInterface;

class Notifier implements NotifierInterface
{
	/**
	 * @var LogService
	 */
	private $logService;

	/**
	 * @var SessionService
	 */
	private $sessionService;

	/**
	 * @param LogService $logService
	 * @param SessionService $sessionService
	 */
	public function __construct(LogService $logService, SessionService $sessionService)
	{
		$this->logService = $logService;
		$this->sessionService = $sessionService;
	}

	/**
	 * @param object $notification
	 */
	public function notify($notification)
	{
		if (!$this->logService->canCreate($notification)){
			return ;
		}

		$this->logService->create($notification);
	}
}
