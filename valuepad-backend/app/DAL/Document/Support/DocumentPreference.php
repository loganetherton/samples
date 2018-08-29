<?php
namespace ValuePad\DAL\Document\Support;

use Illuminate\Config\Repository as Config;
use ValuePad\Core\Document\Interfaces\DocumentPreferenceInterface;
use RuntimeException;

/**
 *
 *
 */
class DocumentPreference implements DocumentPreferenceInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
	 * @return int
     */
    public function getLifetime()
    {
        return $this->config->get('filesystems.documents.lifetime', 10);
    }

	/**
	 * @return string
	 */
	public function getBaseUrl()
	{
		$config = $this->config->get('filesystems');

		$defaultStorage = $config['default'];
		$storageConfig = $config['disks'][$defaultStorage];

		$baseUrl = array_take($storageConfig, 'base_url');

		if (!$baseUrl) {
			throw new RuntimeException('The base url for the "' . $defaultStorage . '" storage is not specified.');
		}

		return $baseUrl;
	}
}
