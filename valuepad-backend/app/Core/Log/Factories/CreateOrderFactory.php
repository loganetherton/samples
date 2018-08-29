<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\CreateOrderNotification;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Enums\Action;

class CreateOrderFactory extends AbstractOrderFactory
{
	/**
	 * @param CreateOrderNotification $notification
	 * @return Log
	 */
	public function create($notification)
	{
		$log = parent::create($notification);

		$log->setAction(new Action(Action::CREATE_ORDER));

		return $log;
	}
}
