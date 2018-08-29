<?php
namespace ValuePad\Core\Log\Factories;
use ValuePad\Core\Appraisal\Notifications\AwardOrderNotification;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Enums\Action;

class AwardOrderFactory extends AbstractOrderFactory
{
    /**
     * @param AwardOrderNotification $notification
     * @return Log
     */
    public function create($notification)
    {
        $log = parent::create($notification);

        $log->setAction(new Action(Action::AWARD_ORDER));

        return $log;
    }
}
