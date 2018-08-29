<?php
namespace ValuePad\Core\User\Interfaces;
use ValuePad\Core\User\Enums\Platform;

interface DevicePreferenceInterface
{
    /**
     * @return string
     */
    public function getAndroidKey();

    /**
     * @param string $token
     * @param Platform $platform
     * @return bool
     */
    public function supports($token, Platform $platform);
}
