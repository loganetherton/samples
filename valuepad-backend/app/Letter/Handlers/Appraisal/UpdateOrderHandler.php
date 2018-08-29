<?php
namespace ValuePad\Letter\Handlers\Appraisal;

use ValuePad\Core\Appraisal\Notifications\AbstractNotification;

class UpdateOrderHandler extends AbstractOrderHandler
{
	/**
	 * @param AbstractNotification $notification
	 * @return string
	 */
	protected function getSubject(AbstractNotification $notification)
	{
		return 'Updated - Order on '.$notification->getOrder()->getProperty()->getDisplayAddress();
	}

	/**
	 * @return string
	 */
	protected function getTemplate()
	{
		return 'emails.appraisal.update_order';
	}
}
