<?php
namespace ValuePad\DAL\Document\Support;

use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Filesystem;
use ValuePad\Core\Document\Support\Storage\FileDescriptor;
use ValuePad\Core\Document\Support\Storage\StorageInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Exception;
use Log;

class Storage implements StorageInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Storage constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string|resource $location
     * @param string $dest
     */
    public function putFileIntoRemoteStorage($location, $dest)
    {
		/**
		 * @var FilesystemFactory $storage
		 */
        $storage = $this->container->make(FilesystemFactory::class);
        $storage->disk()->put($dest, file_get_contents($this->normalizeLocation($location)), Filesystem::VISIBILITY_PUBLIC);
	}

	/**
	 * @param array $uris
	 */
	public function removeFilesFromRemoteStorage(array $uris)
	{
		/**
		 * @var FilesystemFactory $storage
		 */
		$storage = $this->container->make(FilesystemFactory::class);

		foreach ($uris as $uri){
			try {
				$storage->disk()->delete($uri);
			} catch (Exception $ex){
				Log::warning($ex);
			}
		}
	}

    /**
     * @param string|resource $location
     * @return FileDescriptor
     */
    public function getFileDescriptor($location)
    {
        $descriptor = new FileDescriptor();

        $descriptor->setSize(filesize($this->normalizeLocation($location)));

        return $descriptor;
    }

    /**
     * @param string|resource $location
     * @return bool
     */
    public function isFileReadable($location)
    {
        return is_readable($this->normalizeLocation($location));
    }

    /**
     * @param string|resource $location
     * @return string
     */
    private function normalizeLocation($location)
    {
        if (is_resource($location)){
            return stream_get_meta_data($location)['uri'];
        }

        return $location;
    }
}
