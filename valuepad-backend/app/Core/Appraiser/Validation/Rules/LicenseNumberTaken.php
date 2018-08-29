<?php
namespace ValuePad\Core\Appraiser\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Appraiser\Services\AppraiserService;

/**
 *
 *
 */
class LicenseNumberTaken extends AbstractRule
{

    /**
     *
     * @var AppraiserService
     */
    private $appraiserService;

    /**
     *
     * @param AppraiserService $appraiserService
     */
    public function __construct(AppraiserService $appraiserService)
    {
        $this->appraiserService = $appraiserService;
        $this->setIdentifier('already-taken');
        $this->setMessage('The provided license number is already taken by another appraiser.');
    }

    /**
     *
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        list ($value, $state) = $value->extract();

        if ($this->appraiserService->existsWithLicenseNumberInState($value, $state)) {
            return $this->getError();
        }

        return null;
    }
}
