<?php
namespace ValuePad\Core\Appraisal\Notifications;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Entities\Revision;

class RevisionRequestNotification extends AbstractNotification implements UpdateProcessStatusNotificationAwareInterface
{
    use UpdateProcessStatusNotificationAwareTrait;

    /**
     * @var Revision
     */
    private $revision;

    /**
     * @param Order $order
     * @param Revision $revision
     */
    public function __construct(Order $order, Revision $revision)
    {
        parent::__construct($order);

        $this->revision = $revision;
    }

    /**
     * @return Revision
     */
    public function getRevision()
    {
        return $this->revision;
    }
}
