<?php
namespace ValuePad\Core\Asc\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Asc\Services\AscService;

class LicenseNumberExists extends AbstractRule
{
    /**
     * @var AscService
     */
    private $ascService;

    public function __construct(AscService $ascService)
    {
        $this->setIdentifier('exists');
        $this->setMessage('The specified license number belongs to none of appraisers in the asc database.');

        $this->ascService = $ascService;
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        list ($licenseNumber, $state) = $value->extract();

        if (! $this->ascService->existsWithLicenseNumberInState($licenseNumber, $state)) {
            return $this->getError();
        }

        return null;
    }
}
