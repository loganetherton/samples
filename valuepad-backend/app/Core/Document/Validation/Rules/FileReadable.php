<?php
namespace ValuePad\Core\Document\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use ValuePad\Core\Document\Support\Storage\StorageInterface;

/**
 *
 *
 */
class FileReadable extends AbstractRule
{

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;

        $this->setIdentifier('file-access');
        $this->setMessage('Cannot access the uploaded file.');
    }

    /**
     * @param mixed $location
     * @return Error|null
     */
    public function check($location)
    {
        if (! $this->storage->isFileReadable($location)) {
            return $this->getError();
        }

        return null;
    }
}
