<?php
namespace ValuePad\Core\Document\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Document\Support\Storage\StorageInterface;
use ValuePad\Core\Document\Validation\Rules\FileReadable;

/**
 *
 *
 */
class DocumentValidator extends AbstractThrowableValidator
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
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('location', function (Property $property) {
            $property
				->addRule(new Obligate())
                ->addRule(new FileReadable($this->storage));
        });
    }
}
