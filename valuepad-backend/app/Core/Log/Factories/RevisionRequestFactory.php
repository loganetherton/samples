<?php
namespace ValuePad\Core\Log\Factories;
use ValuePad\Core\Appraisal\Notifications\RevisionRequestNotification;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Enums\Action;

class RevisionRequestFactory extends AbstractOrderFactory
{
    /**
     * @param RevisionRequestNotification $notification
     * @return Log
     */
    public function create($notification)
    {
        $log = parent::create($notification);

        $log->setAction(new Action(Action::REVISION_REQUEST));

        return $log;
    }
}
