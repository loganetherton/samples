<?php
namespace ValuePad\Core\Log\Notifications;

use ValuePad\Core\Log\Entities\Log;

class CreateLogNotification
{
	/**
	 * @var Log
	 */
	private $log;

	/**
	 * @param Log $log
	 */
	public function __construct(Log $log)
	{
		$this->log = $log;
	}

	/**
	 * @return Log
	 */
	public function getLog()
	{
		return $this->log;
	}
}
