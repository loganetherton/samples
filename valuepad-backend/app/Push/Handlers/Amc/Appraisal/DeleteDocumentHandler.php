<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\DeleteDocumentNotification;

class DeleteDocumentHandler extends BaseHandler
{
    /**
     * @param DeleteDocumentNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'delete-document';

        $data['document'] = $notification->getDocument()->getId();

        return $data;
    }
}
