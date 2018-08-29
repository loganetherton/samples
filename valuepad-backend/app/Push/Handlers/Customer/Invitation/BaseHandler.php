<?php
namespace ValuePad\Push\Handlers\Customer\Invitation;

use ValuePad\Core\Invitation\Notifications\AbstractNotification;
use ValuePad\Push\Support\AbstractHandler;
use ValuePad\Push\Support\Call;

abstract class BaseHandler extends AbstractHandler
{
	/**
	 * @param AbstractNotification $notification
	 * @return array
	 */
	protected function getCalls($notification)
	{
		$customer = $notification->getInvitation()->getCustomer();

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
