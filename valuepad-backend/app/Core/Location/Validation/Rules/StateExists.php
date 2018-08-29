<?php
namespace ValuePad\Core\Location\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Location\Services\StateService;

/**
 *
 *
 */
class StateExists extends AbstractRule
{
    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @param StateService $stateService
     */
    public function __construct(StateService $stateService)
    {
        $this->stateService = $stateService;

        $this->setIdentifier('exists');
        $this->setMessage('The state does not exist.');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        if (! $this->stateService->exists($value)) {
            return $this->getError();
        }

        return null;
    }
}
