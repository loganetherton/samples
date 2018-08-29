<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Log\Notifications\CreateLogNotification;
use ValuePad\Push\Support\AbstractHandler;
use ValuePad\Push\Support\Call;

class CreateLogHandler extends AbstractHandler
{
    use CallsCapableTrait;

    /**
     * @param CreateLogNotification $notification
     * @return Call[]
     */
    protected function getCalls($notification)
    {
        $amc = $notification->getLog()->getAssignee();

        if (!$amc instanceof Amc){
            return [];
        }

        return $this->createCalls($amc);
    }

    /**
     * @param CreateLogNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        return [
            'type' => 'order',
            'event' => 'create-log',
            'order' => object_take($notification, 'log.order.id'),
            'log' => $notification->getLog()->getId()
        ];
    }
}
