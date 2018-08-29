<?php
namespace ValuePad\Core\User\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use ValuePad\Core\Appraiser\Services\AppraiserService;

class UserIsAppraiser extends AbstractRule
{
    /**
     * @var AppraiserService
     */
    private $appraiserService;

    /**
     * @param AppraiserService $appraiserService
     */
    public function __construct(AppraiserService $appraiserService)
    {
        $this->appraiserService = $appraiserService;
        $this->setIdentifier('not-appraiser');
        $this->setMessage('The provided user is not an appraiser.');
    }

    /**
     * Checks whether the given user ID belongs to an Appraiser
     *
     * @param int $value
     * @return null|Error
     */
    public function check($value)
    {
        if (! $this->appraiserService->exists($value)) {
            return $this->getError();
        }

        return null;
    }
}
