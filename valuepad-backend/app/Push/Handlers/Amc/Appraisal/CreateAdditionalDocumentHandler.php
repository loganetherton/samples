<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\CreateAdditionalDocumentNotification;

class CreateAdditionalDocumentHandler extends BaseHandler
{
    /**
     * @param CreateAdditionalDocumentNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'create-additional-document';

        $data['additionalDocument'] = $notification->getAdditionalDocument()->getId();

        return $data;
    }
}
