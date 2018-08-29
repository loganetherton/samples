<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\UpdateOrderNotification;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Enums\Action;

class UpdateOrderFactory extends AbstractOrderFactory
{
	/**
	 * @param UpdateOrderNotification $notification
	 * @return Log
	 */
	public function create($notification)
	{
		$log = parent::create($notification);

		$log->setAction(new Action(Action::UPDATE_ORDER));

		return $log;
	}
}
