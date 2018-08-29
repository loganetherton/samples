<?php
namespace ValuePad\Letter\Handlers\Appraisal;

use ValuePad\Core\Appraisal\Notifications\AbstractNotification;

class BidRequestHandler extends AbstractOrderHandler
{
	/**
	 * @param AbstractNotification $notification
	 * @return array
	 */
	protected function getData(AbstractNotification $notification)
	{
		$data = parent::getData($notification);

		$data['instruction'] = $notification->getOrder()->getInstruction();

		return $data;
	}

	/**
	 * @param AbstractNotification $notification
	 * @return string
	 */
	protected function getSubject(AbstractNotification $notification)
	{
		return 'Bid Request - Order on '.$notification->getOrder()->getProperty()->getDisplayAddress();
	}

	/**
	 * @return string
	 */
	protected function getTemplate()
	{
		return 'emails.appraisal.bid_request';
	}
}
