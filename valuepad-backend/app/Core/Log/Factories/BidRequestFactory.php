<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\BidRequestNotification;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Enums\Action;

class BidRequestFactory extends AbstractOrderFactory
{
	/**
	 * @param BidRequestNotification $notification
	 * @return Log
	 */
	public function create($notification)
	{
		$log = parent::create($notification);

		$log->setAction(new Action(Action::BID_REQUEST));

		return $log;
	}
}
