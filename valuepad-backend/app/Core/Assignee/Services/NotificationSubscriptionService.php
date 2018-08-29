<?php
namespace ValuePad\Core\Assignee\Services;

use ValuePad\Core\Assignee\Entities\NotificationSubscription;
use ValuePad\Core\Assignee\Persistables\NotificationSubscriptionPersistable;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\User\Entities\User;

class NotificationSubscriptionService extends AbstractService
{
	/**
	 * @param int $assigneeId
	 * @return NotificationSubscription[]
	 */
	public function getAll($assigneeId)
	{
		return $this->entityManager
			->getRepository(NotificationSubscription::class)
			->findBy(['assignee' => $assigneeId]);
	}

	/**
	 * @param int $assigneeId
	 * @param NotificationSubscriptionPersistable[] $persistables
	 */
	public function updateBySelectedCustomers($assigneeId, array $persistables)
	{
		/**
		 * @var NotificationSubscription[] $subscriptions
		 */
		$subscriptions = $this->entityManager
			->getRepository(NotificationSubscription::class)
			->retrieveAll(['assignee' => $assigneeId, 'customer' =>  ['in', array_keys($persistables)]]);

		foreach ($subscriptions as $subscription){
			$customerId = $subscription->getCustomer()->getId();

			if (($email = $persistables[$customerId]->getEmail()) !== null){
				$subscription->setEmail($email);
			}
		}

		$this->entityManager->flush();
	}

	/**
	 * @param int $assigneeId
	 * @param int $customerId
	 * @return NotificationSubscription
	 */
	public function getByCustomerId($assigneeId, $customerId)
	{
		return $this->entityManager->getRepository(NotificationSubscription::class)
			->findOneBy(['customer' => $customerId, 'assignee' => $assigneeId]);
	}

    /**
     * @param int $assigneeId
     * @param int $customerId
     * @return NotificationSubscription
     */
	public function subscribe($assigneeId, $customerId)
    {
        $subscription = new NotificationSubscription();
        $subscription->setAssignee($this->entityManager->getReference(User::class, $assigneeId));
        $subscription->setCustomer($this->entityManager->getReference(Customer::class, $customerId));
        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        return $subscription;
    }

    /**
     * @param int $assigneeId
     * @param int $customerId
     * @return NotificationSubscription
     */
    public function subscribeIfNot($assigneeId, $customerId)
    {
        $isSubscribed = $this->entityManager->getRepository(NotificationSubscription::class)
            ->exists(['customer' => $customerId, 'assignee' => $assigneeId]);

        if ($isSubscribed){
            return null;
        }

        return $this->subscribe($assigneeId, $customerId);
    }
}
