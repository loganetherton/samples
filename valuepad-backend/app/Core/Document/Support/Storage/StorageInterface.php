<?php
namespace ValuePad\Core\Document\Support\Storage;

/**
 *
 *
 */
interface StorageInterface
{
    /**
     * @param string|resource $location
     * @param string $dest
     */
    public function putFileIntoRemoteStorage($location, $dest);

	/**
	 * @param array $uris
	 */
	public function removeFilesFromRemoteStorage(array $uris);

    /**
     *
     * @param string|resource $location
     * @return bool
     */
    public function isFileReadable($location);

    /**
     *
     * @param string|resource $location
     * @return FileDescriptor
     */
    public function getFileDescriptor($location);
}
