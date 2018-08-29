<?php
namespace ValuePad\Push\Handlers\Customer\Appraisal;

use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Push\Support\AbstractHandler;
use ValuePad\Push\Support\Call;

abstract class BaseHandler extends AbstractHandler
{
	/**
	 * @param AbstractNotification $notification
	 * @return Call[]
	 */
	public function getCalls($notification)
	{
		$customer = $notification->getOrder()->getCustomer();

		$url = $customer->getSettings()->getPushUrl();

		if ($url === null){
			return [];
		}

		$call = new Call();

		$call->setUrl($url);
		$call->setSecret1($customer->getSecret1());
		$call->setSecret2($customer->getSecret2());
		$call->setUser($customer);

		return [$call];
	}
}
