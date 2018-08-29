<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Extras\Extra;
use ValuePad\Core\Log\Extras\LocationExtra;

class AbstractOrderFactory extends AbstractFactory
{
	/**
	 * @param AbstractNotification $notification
	 * @return Log
	 */
	public function create($notification)
	{
		$log = parent::create($notification);

		/**
		 * @var Extra $extra
		 */
		$extra = $log->getExtra();

		$extra[Extra::CUSTOMER] = $log->getOrder()->getCustomer()->getName();
		$extra->merge(LocationExtra::fromProperty($notification->getOrder()->getProperty()));

		return $log;
	}
}
