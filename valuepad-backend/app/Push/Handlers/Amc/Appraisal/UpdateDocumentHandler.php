<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\UpdateDocumentNotification;

class UpdateDocumentHandler extends BaseHandler
{
    /**
     * @param UpdateDocumentNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'update-document';
        $data['document'] = $notification->getDocument()->getId();

        return $data;
    }
}
