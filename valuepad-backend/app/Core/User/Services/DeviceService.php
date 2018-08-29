<?php
namespace ValuePad\Core\User\Services;

use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\User\Entities\Device;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\User\Interfaces\DevicePreferenceInterface;
use ValuePad\Core\User\Persistables\DevicePersistable;
use ValuePad\Core\User\Validation\DeviceValidator;

class DeviceService extends AbstractService
{
	/**
	 * @param array $userIds
	 * @return Device[]
	 */
	public function getAllByUserIds(array $userIds)
	{
		return $this->entityManager
			->getRepository(Device::class)
			->retrieveAll(['user' => ['in', $userIds]]);
	}

	/**
	 * @param int $userId
	 * @param DevicePersistable $persistable
	 * @return Device
	 */
	public function createIfNeeded($userId, DevicePersistable $persistable)
	{
		/**
		 * @var DevicePreferenceInterface $preference
		 */
		$preference = $this->container->get(DevicePreferenceInterface::class);

		(new DeviceValidator($preference))->validate($persistable);

		/**
		 * @var Device $device
		 */
		$device = $this->entityManager->getRepository(Device::class)
			->findOneBy([
				'token' => $persistable->getToken(),
				'platform' => $persistable->getPlatform()
			]);

		if ($device){
			if ($device->getUser()->getId() == $userId){
				return $device;
			}

			$this->entityManager->remove($device);
		}

		/**
		 * @var User $user
		 */
		$user = $this->entityManager->find(User::class, $userId);

		$device = new Device();
		$this->transfer($persistable, $device);

		$device->setUser($user);

		$this->entityManager->persist($device);
		$this->entityManager->flush();

		return $device;
	}

	/**
	 * @param int $id
	 */
	public function delete($id)
	{
		$this->entityManager->getRepository(Device::class)->delete(['id' => $id]);
	}

    /**
     * @param string $token
     */
	public function deleteByToken($token)
    {
        $this->entityManager->getRepository(Device::class)->delete(['token' => $token]);
    }
}
