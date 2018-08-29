<?php
namespace ValuePad\Letter\Handlers\Appraisal;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Appraisal\Notifications\AcceptOrderWithConditionsNotification;

class AcceptOrderWithConditionsHandler extends AbstractOrderHandler
{
    /**
     * @param AbstractNotification $notification
     * @return string
     */
    protected function getSubject(AbstractNotification $notification)
    {
        return 'Accepted With Conditions - Order on '.$notification->getOrder()->getProperty()->getDisplayAddress();
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return 'emails.appraisal.accept_order_with_conditions';
    }

    /**
     * @param AbstractNotification|AcceptOrderWithConditionsNotification $notification
     * @return array
     */
    protected function getData(AbstractNotification $notification)
    {
        $data = parent::getData($notification);

        $conditions = $notification->getConditions();

        $data['conditions'] = [
            'reason' => ucfirst(str_replace('-', ' ', (string) $conditions->getRequest())),
            'fee' => $conditions->getFee(),
            'explanation' => $conditions->getExplanation(),
            'dueDate' => null
        ];

        if ($dueDate = $conditions->getDueDate()){
            $data['conditions']['dueDate'] = $dueDate->format('m/d/Y');
        }

        return $data;
    }
}
