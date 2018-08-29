<?php
namespace ValuePad\Letter\Handlers\Appraisal;

use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Appraisal\Notifications\SendMessageNotification;

class SendMessageHandler extends AbstractOrderHandler
{
	/**
	 * @param AbstractNotification|SendMessageNotification $notification
	 * @return string
	 */
	protected function getSubject(AbstractNotification $notification)
	{
		return 'Message - Order on '.$notification->getOrder()->getProperty()->getDisplayAddress();
	}

	/**
	 * @return string
	 */
	protected function getTemplate()
	{
		return 'emails.appraisal.send_message';
	}

	/**
	 * @param AbstractNotification|SendMessageNotification $notification
	 * @return array
	 */
	protected function getData(AbstractNotification $notification)
	{
		$data = parent::getData($notification);

		$data['content'] = $notification->getMessage()->getContent();

		return $data;
	}

	/**
	 * @param AbstractNotification|SendMessageNotification $notification
	 * @return string
	 */
	protected function getActionUrl(AbstractNotification $notification)
	{
		return $this->config->get('app.front_end_url').'/orders/details/'.$notification->getOrder()->getId().'/messages';
	}
}
