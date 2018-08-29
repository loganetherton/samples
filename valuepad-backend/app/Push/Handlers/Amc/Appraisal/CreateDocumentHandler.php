<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification;

class CreateDocumentHandler extends BaseHandler
{
    /**
     * @param CreateDocumentNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'create-document';

        $data['document'] = $notification->getDocument()->getId();

        return $data;
    }
}
