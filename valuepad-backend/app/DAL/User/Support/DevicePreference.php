<?php
namespace ValuePad\DAL\User\Support;
use Illuminate\Container\Container;
use ValuePad\Core\User\Enums\Platform;
use ValuePad\Core\User\Interfaces\DevicePreferenceInterface;
use Illuminate\Config\Repository as Config;
use ValuePad\Mobile\Support\AdapterFactory;

class DevicePreference implements DevicePreferenceInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Config $config
     * @param Container $container
     */
    public function __construct(Config $config, Container $container)
    {
        $this->config = $config;
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getAndroidKey()
    {
        return $this->config->get('app.push_notifications.android.key');
    }

    /**
     * @param string $token
     * @param Platform $platform
     * @return bool
     */
    public function supports($token, Platform $platform)
    {
        /**
         * @var AdapterFactory $adapter
         */
        $adapter = $this->container->make(AdapterFactory::class);

        if ($platform->is(Platform::ANDROID)){
            return $adapter->android()->supports($token);
        }

        if ($platform->is(Platform::IOS)){
            return $adapter->ios()->supports($token);
        }

        return false;
    }
}
