<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\DeleteOrderNotification;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Enums\Action;

class DeleteOrderFactory extends AbstractOrderFactory
{
	/**
	 * @param DeleteOrderNotification $notification
	 * @return Log
	 */
	public function create($notification)
	{
		$log = parent::create($notification);

		$log->setOrder(null);

		$log->setAction(new Action(Action::DELETE_ORDER));

		return $log;
	}
}
