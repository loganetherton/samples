<?php
namespace ValuePad\Core\Customer\Services;

use ValuePad\Core\Customer\Entities\Settings;
use ValuePad\Core\Customer\Persistables\SettingsPersistable;
use ValuePad\Core\Customer\Validation\SettingsValidator;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Service\AbstractService;

class SettingsService extends AbstractService
{
	/**
	 * @param int $customerId
	 * @param SettingsPersistable $persistable
	 * @param UpdateOptions $options = null
	 */
	public function update($customerId, SettingsPersistable $persistable, UpdateOptions $options = null)
	{
		if ($options === null){
			$options = new UpdateOptions();
		}

		(new SettingsValidator())
			->setForcedProperties($options->getPropertiesScheduledToClear())
			->validate($persistable, true);

		/**
		 * @var Settings $settings
		 */
		$settings = $this->entityManager->find(Settings::class, $customerId);

		$this->transfer($persistable, $settings, [
			'nullable' => $options->getPropertiesScheduledToClear()
		]);

		$this->entityManager->flush();
	}

	/**
	 * @param int $customerId
	 * @return Settings
	 */
	public function get($customerId)
	{
		return $this->entityManager->find(Settings::class, $customerId);
	}

	/**
	 * @param array $customerIds
	 * @return Settings[]
	 */
	public function getAllBySelectedCustomers(array $customerIds)
	{
		return $this->entityManager->getRepository(Settings::class)
			->retrieveAll(['customer' => ['in', $customerIds]]);
	}
}
