<?php
namespace ValuePad\Core\Company\Services;

use DateTime;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\Company\Options\CreateManagerOptions;
use ValuePad\Core\Company\Persistables\ManagerPersistable;
use ValuePad\Core\Company\Validation\ManagerValidator;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Shared\Entities\Availability;
use ValuePad\Core\Shared\Persistables\AvailabilityPersistable;
use ValuePad\Core\Shared\Validation\AvailabilityValidator;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\User\Interfaces\PasswordEncryptorInterface;

class ManagerService extends AbstractService
{
    /**
     * @param int $id
     * @return Manager
     */
    public function get($id)
    {
        return $this->entityManager->find(Manager::class, $id);
    }

    /**
     * @param ManagerPersistable $persistable
     * @param CreateManagerOptions $options
     * @return Manager
     */
    public function create(ManagerPersistable $persistable, CreateManagerOptions $options = null, array $nullable = [])
    {
        if ($options === null){
            $options = new CreateManagerOptions();
        }

        if (!$options->isTrusted()){
            (new ManagerValidator($this->container))->validate($persistable);
        }

        $manager = new Manager();

        $availability = $this->saveAvailability(
            $persistable->getAvailability() ?? new AvailabilityPersistable(),
            new Availability(),
            array_map(function($value){
                return cut_string_left($value, 'availability.');
            }, array_filter($nullable, function($v){
                return starts_with($v, 'availability.');
            }))
        );

        $this->exchange($persistable, $manager);

        $manager->setAvailability($availability);

        $this->entityManager->persist($manager);

        $this->entityManager->flush();

        return $manager;
    }

    /**
     * @param int $id
     * @param ManagerPersistable $persistable
     * @param UpdateOptions $options
     */
    public function update($id, ManagerPersistable $persistable, UpdateOptions $options = null)
    {
        if ($options === null){
            $options = new UpdateOptions();
        }

        $nullable = $options->getPropertiesScheduledToClear();

        /**
         * @var Manager $manager
         */
        $manager = $this->entityManager->find(Manager::class, $id);

        (new ManagerValidator($this->container))
            ->setCurrentManager($manager)
            ->setForcedProperties($nullable)
            ->validate($persistable, true);

        $this->exchange($persistable, $manager, $nullable);

        $this->entityManager->flush();
    }

    /**
     * @param ManagerPersistable $persistable
     * @param Manager $manager
     * @param array $nullable
     */
    private function exchange(ManagerPersistable $persistable, Manager $manager, array $nullable = [])
    {
        $this->transfer($persistable, $manager, [
            'nullable' => $nullable,
            'ignore' => ['password']
        ]);

        if ($password = $persistable->getPassword()){
            /**
             * @var PasswordEncryptorInterface $encrypter
             */
            $encrypter = $this->container->get(PasswordEncryptorInterface::class);
            $manager->setPassword($encrypter->encrypt($password));
        }
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $manager = $this->entityManager->getReference(Manager::class, $id);
        $this->entityManager->remove($manager);
        $this->entityManager->flush();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->entityManager->getRepository(Manager::class)->exists(['id' => $id]);
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @return bool
     */
    public function hasOrder($managerId, $orderId)
    {
        /**
         * @var OrderService $orderService
         */
        $orderService = $this->container->get(OrderService::class);

        return $orderService->existsByAssigneeId($orderId, $managerId, true);
    }

    /**
     * @param int $managerId
     * @param AvailabilityPersistable $persistable
     * @param UpdateOptions $options
     */
    public function updateAvailability(
        $managerId,
        AvailabilityPersistable $persistable,
        UpdateOptions $options = null
    )
    {
        if ($options === null){
            $options = new UpdateOptions();
        }

        /**
         * @var Manager $manager
         */
        $manager = $this->entityManager->find(Manager::class, $managerId);
        $availability = $manager->getAvailability();

        if (!$availability) {
            $availability = new Availability();
        }

        (new AvailabilityValidator())
            ->setForcedProperties($options->getPropertiesScheduledToClear())
            ->validateWithAvailability($persistable, $availability);

        $manager->setUpdatedAt(new DateTime());

        $this->saveAvailability($persistable, $availability, $options->getPropertiesScheduledToClear());

        // Save availability ID on manager record
        $manager->setAvailability($availability);
        $this->entityManager->persist($manager);
        $this->entityManager->flush();
    }

    /**
     * @param AvailabilityPersistable $persistable
     * @param Availability $availability
     * @param array $nullable
     */
    private function saveAvailability(
        AvailabilityPersistable $persistable,
        Availability $availability,
        array $nullable = []
    )
    {
        $this->transfer($persistable, $availability, ['nullable' => $nullable]);

        if ($availability->getId() === null){
            $this->entityManager->persist($availability);
        }

        $this->entityManager->flush();

        return $availability;
    }

    /**
     * Checks whether a given manager is related to the specified customer
     *
     * @param int $managerId
     * @param int $customerId
     * @return bool
     */
    public function isRelatedWithCustomer($managerId, $customerId)
    {
        return $this->entityManager->getRepository(Manager::class)
            ->exists(['customers' => ['HAVE MEMBER', $customerId], 'id' => $managerId]);
    }
}
