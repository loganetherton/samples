<?php
namespace ValuePad\Api\Support\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 *
 */
class Document extends AbstractRule
{

    /**
     * File constructor.
     */
    public function __construct()
    {
        $this->setIdentifier('document');
        $this->setMessage('Document is not attached.');
    }

    /**
     *
     * @param mixed $value
     * @return Error|null
     */
    public function check($value)
    {
        if (! $value instanceof UploadedFile) {
            return $this->getError();
        }

        return null;
    }
}
