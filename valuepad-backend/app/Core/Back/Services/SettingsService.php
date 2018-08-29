<?php
namespace ValuePad\Core\Back\Services;

use ValuePad\Core\Back\Entities\Setting;
use ValuePad\Core\Support\Service\AbstractService;

class SettingsService extends AbstractService
{
	const SETTING_MASTER_PASSWORD = 'master_password';

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value)
	{
		/**
		 * @var Setting $setting
		 */
		$setting = $this->entityManager->find(Setting::class, $name);

		if ($setting === null){
			$setting = new Setting();
			$setting->setName($name);
			$this->entityManager->persist($setting);
		}

		$setting->setValue($value);

		$this->entityManager->flush();
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function get($name)
	{
		/**
		 * @var Setting $setting
		 */
		$setting = $this->entityManager->find(Setting::class, $name);

		if (!$setting){
			return null;
		}

		return $setting->getValue();
	}
}
