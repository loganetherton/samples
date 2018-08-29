<?php
namespace ValuePad\Api\Appraisal\V2_0\Support;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use ValuePad\Core\Appraisal\Exceptions\AdditionalStatusForbiddenException;

trait AdditionalStatusesTrait
{
    /**
     * @param callable $callback
     */
    public function tryChangeAdditionalStatus(callable $callback)
    {
        try {
            $callback();
        } catch (AdditionalStatusForbiddenException $ex) {
            $errors = new ErrorsThrowableCollection();
            $errors['additionalStatus'] = new Error('permissions', $ex->getMessage());
            throw $errors;
        }
    }
}
