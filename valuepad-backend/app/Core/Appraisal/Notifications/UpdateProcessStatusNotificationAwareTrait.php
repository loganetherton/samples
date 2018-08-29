<?php
namespace ValuePad\Core\Appraisal\Notifications;

trait UpdateProcessStatusNotificationAwareTrait
{
    /**
     * @var UpdateProcessStatusNotification
     */
    private $updateProcessStatusNotification;

    /**
     * @param UpdateProcessStatusNotification $notification
     */
    public function setUpdateProcessStatusNotification(UpdateProcessStatusNotification $notification)
    {
        $this->updateProcessStatusNotification = $notification;
    }

    /**
     * @return UpdateProcessStatusNotification
     */
    public function getUpdateProcessStatusNotification()
    {
        return $this->updateProcessStatusNotification;
    }
}
