<?php
namespace ValuePad\Letter\Handlers\Appraisal;

use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Appraisal\Notifications\DeleteAdditionalDocumentNotification;

class DeleteAdditionalDocumentHandler extends AbstractOrderHandler
{
	/**
	 * @param AbstractNotification|DeleteAdditionalDocumentNotification $notification
	 * @return string
	 */
	protected function getSubject(AbstractNotification $notification)
	{
		return 'Additional Document Deleted - Order on '
			.$notification->getOrder()->getProperty()->getDisplayAddress();
	}

	/**
	 * @param AbstractNotification|DeleteAdditionalDocumentNotification $notification
	 * @return array
	 */
	protected function getData(AbstractNotification $notification)
	{
		$data = parent::getData($notification);

		$data['document'] = $notification->getAdditionalDocument()->getDocument()->getName();

		return $data;
	}

	/**
	 * @return string
	 */
	protected function getTemplate()
	{
		return 'emails.appraisal.delete_additional_document';
	}
}
