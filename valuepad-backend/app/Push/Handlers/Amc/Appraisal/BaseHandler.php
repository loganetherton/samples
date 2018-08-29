<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Push\Support\AbstractHandler;
use ValuePad\Push\Support\Call;

abstract class BaseHandler extends AbstractHandler
{
    use CallsCapableTrait;

    /**
     * @param AbstractNotification $notification
     * @return Call[]
     */
    protected function getCalls($notification)
    {
        $amc = $notification->getOrder()->getAssignee();

        if (!$amc instanceof Amc){
            return [];
        }

        return $this->createCalls($amc);
    }

    /**
     * @param AbstractNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        return [
            'type' => 'order',
            'order' => $notification->getOrder()->getId()
        ];
    }
}
