<?php
namespace ValuePad\Mobile\Handlers;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Mobile\Support\HandlerInterface;
use ValuePad\Mobile\Support\News;
use ValuePad\Mobile\Support\Tuple;

abstract class AbstractOrderHandler implements HandlerInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param AbstractNotification $notification
     * @return Tuple
     */
    public function handle($notification)
    {
        $order = $notification->getOrder();

        $assignee = $order->getAssignee();

        if ($this->session->getUser()->getId() === $assignee->getId()){
            return null;
        }

        $news = new News();

        $news->setCategory('order');
        $news->setName($this->getName());
        $news->setExtra($this->getExtra($notification));

        $news->setMessage($this->getMessage($notification));

        return new Tuple([$assignee], $news);
    }

    /**
     * @param AbstractNotification $notification
     * @return string
     */
    abstract protected function getMessage($notification);

    /**
     * @return string
     */
    abstract protected function getName();

    /**
     * @param AbstractNotification $notification
     * @return array
     */
    protected function getExtra($notification)
    {
        $order = $notification->getOrder();

        return [
            'order' => $order->getId(),
        ];
    }
}
