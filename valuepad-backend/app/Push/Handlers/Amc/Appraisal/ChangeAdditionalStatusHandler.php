<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\ChangeAdditionalStatusNotification;

class ChangeAdditionalStatusHandler extends BaseHandler
{
    /**
     * @param ChangeAdditionalStatusNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'change-additional-status';

        $data = array_merge($data, [
            'oldAdditionalStatus' => object_take($notification->getOldAdditionalStatus(), 'id'),
            'oldAdditionalStatusComment' => $notification->getOldAdditionalStatusComment(),
            'newAdditionalStatus' => object_take($notification->getNewAdditionalStatus(), 'id'),
            'newAdditionalStatusComment' => $notification->getNewAdditionalStatusComment(),
        ]);

        return $data;
    }
}
