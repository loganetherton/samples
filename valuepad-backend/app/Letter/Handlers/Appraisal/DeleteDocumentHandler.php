<?php
namespace ValuePad\Letter\Handlers\Appraisal;

use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification;

class DeleteDocumentHandler extends AbstractOrderHandler
{
	/**
	 * @param AbstractNotification|CreateDocumentNotification $notification
	 * @return string
	 */
	protected function getSubject(AbstractNotification $notification)
	{
		return 'Document Deleted - Order on '
			.$notification->getOrder()->getProperty()->getDisplayAddress();
	}

	/**
	 * @param AbstractNotification|CreateDocumentNotification $notification
	 * @return array
	 */
	protected function getData(AbstractNotification $notification)
	{
		$data = parent::getData($notification);

		$data['document'] = $notification->getDocument()->getPrimary()->getName();

		return $data;
	}

	/**
	 * @return string
	 */
	protected function getTemplate()
	{
		return 'emails.appraisal.delete_document';
	}
}
