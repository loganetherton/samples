<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\DeleteAdditionalDocumentNotification;

class DeleteAdditionalDocumentHandler extends BaseHandler
{
    /**
     * @param DeleteAdditionalDocumentNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'delete-additional-document';

        $data['additionalDocument'] = $notification->getAdditionalDocument()->getId();

        return $data;
    }
}
