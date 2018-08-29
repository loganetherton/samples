<?php
namespace ValuePad\Core\User\Properties\Device;

use ValuePad\Core\User\Enums\Platform;

trait PlatformPropertyTrait
{
	/**
	 * @var Platform
	 */
	private $platform;

	/**
	 * @param Platform $platform
	 */
	public function setPlatform(Platform $platform)
	{
		$this->platform = $platform;
	}

	public function getPlatform()
	{
		return $this->platform;
	}
}
