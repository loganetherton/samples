<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\RevisionRequestNotification;

class RevisionRequestHandler extends BaseHandler
{
    /**
     * @param RevisionRequestNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'revision-request';
        $data['revision'] = $notification->getRevision()->getId();

        return $data;
    }
}
