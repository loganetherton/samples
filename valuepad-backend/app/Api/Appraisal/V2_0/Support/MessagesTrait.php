<?php
namespace ValuePad\Api\Appraisal\V2_0\Support;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use ValuePad\Core\Appraisal\Exceptions\ReaderNotRelatedException;

trait MessagesTrait
{
    /**
     * @param callable $callback
     * @throws ErrorsThrowableCollection
     */
    protected function tryMarkAsRead(callable  $callback)
    {
        try {
            $callback();
        } catch (ReaderNotRelatedException $ex){
            $errors = new ErrorsThrowableCollection();

            $errors['messages'] = new Error('permissions', $errors->getMessage());

            throw $errors;
        }
    }
}
