<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use Ascope\Libraries\Transformer\SharedModifiers;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification;

class UpdateProcessStatusHandler extends BaseHandler
{
    /**
     * @param UpdateProcessStatusNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'update-process-status';

        $data['oldProcessStatus'] = (string) $notification->getOldProcessStatus();
        $data['newProcessStatus'] = (string) $notification->getNewProcessStatus();

        if ($notification->getNewProcessStatus()->is(ProcessStatus::INSPECTION_SCHEDULED)){
            $modifier = new SharedModifiers();

            $data['scheduledAt'] = $modifier->datetime(
                $notification->getExtra()[UpdateProcessStatusNotification::EXTRA_SCHEDULED_AT]);

            $data['estimatedCompletionDate'] = $modifier->datetime(
                $notification->getExtra()[UpdateProcessStatusNotification::EXTRA_ESTIMATED_COMPLETION_DATE]);
        }

        if ($notification->getNewProcessStatus()->is(ProcessStatus::INSPECTION_COMPLETED)){
            $modifier = new SharedModifiers();

            $data['completedAt'] = $modifier->datetime(
                $notification->getExtra()[UpdateProcessStatusNotification::EXTRA_COMPLETED_AT]);

            $data['estimatedCompletionDate'] = $modifier->datetime(
                $notification->getExtra()[UpdateProcessStatusNotification::EXTRA_ESTIMATED_COMPLETION_DATE]);
        }

        return $data;
    }
}
