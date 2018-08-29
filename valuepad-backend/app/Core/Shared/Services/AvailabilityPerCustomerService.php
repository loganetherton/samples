<?php
namespace ValuePad\Core\Shared\Services;

use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Shared\Entities\AvailabilityPerCustomer;
use ValuePad\Core\Shared\Notifications\AvailabilityPerCustomerNotification;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Shared\Persistables\AvailabilityPersistable;
use ValuePad\Core\Shared\Validation\AvailabilityValidator;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\User\Entities\User;

class AvailabilityPerCustomerService extends AbstractService
{
    /**
     * Given a user ID and a customer ID, returns the user's availability for that specific customer
     *
     * @param int $userId
     * @param int $customerId
     * @return AvailabilityPerCustomer
     */
    public function getByUserAndCustomerId($userId, $customerId)
    {
        $availability = $this->entityManager->getRepository(AvailabilityPerCustomer::class)
            ->retrieve(['user' => $userId, 'customer' => $customerId]);

        if (! $availability) {
            $availability = new AvailabilityPerCustomer();
            $user = $this->entityManager->find(User::class, $userId);
            $this->transfer($user->getAvailability(), $availability, ['ignore' => ['id']]);
        }

        return $availability;
    }

    /**
     * Creates or updates availability
     *
     * @param int $userId
     * @param int $customerId
     * @param AvailabilityPersistable $persistable
     * @param UpdateOptions $options
     * @return AvailabilityPerCustomer
     */
    public function replace(
        $userId,
        $customerId,
        AvailabilityPersistable $persistable,
        UpdateOptions $options = null
    ) {
        $availability = $this->getByUserAndCustomerId($userId, $customerId);

        if ($options === null) {
            $options = new UpdateOptions();
        }

        (new AvailabilityValidator())
            ->setForcedProperties($options->getPropertiesScheduledToClear())
            ->validateWithAvailability($persistable, $availability);

        $this->transfer($persistable, $availability);

        if ($availability->getId() === null) {
            $user = $this->entityManager->find(User::class, $userId);
            $customer = $this->entityManager->find(Customer::class, $customerId);
            $availability->setUser($user);
            $availability->setCustomer($customer);
            $this->entityManager->persist($availability);
        }

        $this->notify(new AvailabilityPerCustomerNotification($availability));

        $this->entityManager->flush();

        return $availability;
    }
}
