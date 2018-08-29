<?php
namespace ValuePad\Api\Appraisal\V2_0\Support;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use ValuePad\Core\Appraisal\Entities\Document;
use ValuePad\Core\Appraisal\Exceptions\ExtractFailedException;

trait DocumentsTrait
{
    /** @noinspection PhpInconsistentReturnPointsInspection */

    /**
     * @param callable $callback
     * @throws ErrorsThrowableCollection
     * @return Document
     *
     */
    protected function tryCreate(callable  $callback)
    {
        try {
            return $callback();
        } catch (ExtractFailedException $ex){
            ErrorsThrowableCollection::throwError('primary', new Error('invalid', $ex->getMessage()));
        }
    }
}
