<?php
namespace ValuePad\Core\Appraisal\Services;
use ValuePad\Core\Appraisal\Emails\UnacceptedReminderEmail;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Support\Letter\EmailerInterface;
use ValuePad\Core\Support\Letter\LetterPreferenceInterface;
use ValuePad\Core\Support\Service\AbstractService;
use DateTime;

class ReminderService extends AbstractService
{
    public function handleAllUnacceptedOrders()
    {
        $builder = $this->entityManager->createQueryBuilder();

        $orders = $builder
            ->select('o', 'd', 'c', 's')
            ->from(Order::class, 'o')
            ->andWhere($builder->expr()->in('o.processStatus', ':processStatuses'))
            ->setParameter('processStatuses', [ProcessStatus::FRESH, ProcessStatus::REQUEST_FOR_BID])
            ->join('o.customer', 'c')
            ->join('c.settings', 's')
            ->join('o.supportingDetails', 'd')
            ->andWhere($builder->expr()->isNotNull('s.unacceptedReminder'))
            ->andWhere('CURRENT_DATE() >= DATE_ADD(o.orderedAt, s.unacceptedReminder, \'HOUR\')')
            ->andWhere($builder->expr()->isNull('d.unacceptedRemindedAt'))
            ->getQuery()
            ->getResult();


        /**
         * @var EmailerInterface $emailer
         */
        $emailer = $this->container->get(EmailerInterface::class);

        /**
         * @var LetterPreferenceInterface $preference
         */
        $preference = $this->container->get(LetterPreferenceInterface::class);

        /**
         * @var Order[] $orders
         */
        foreach ($orders as $order){

            $email = new UnacceptedReminderEmail($order);

            $email->setSender($preference->getNoReply(), $preference->getSignature());
            $email->addRecipient($order->getAssignee()->getEmail(), $order->getAssignee()->getDisplayName());

            $emailer->send($email);

            $order->getSupportingDetails()->setUnacceptedRemindedAt(new DateTime());

            $this->entityManager->flush();
        }
    }
}
