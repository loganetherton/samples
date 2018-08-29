<?php
namespace ValuePad\Core\Log\Factories;
use ValuePad\Core\Appraisal\Notifications\ReconsiderationRequestNotification;
use ValuePad\Core\Log\Enums\Action;

class ReconsiderationRequestFactory extends AbstractOrderFactory
{
    /**
     * @param ReconsiderationRequestNotification $notification
     * @return Log
     */
    public function create($notification)
    {
        $log = parent::create($notification);

        $log->setAction(new Action(Action::RECONSIDERATION_REQUEST));

        return $log;
    }
}
