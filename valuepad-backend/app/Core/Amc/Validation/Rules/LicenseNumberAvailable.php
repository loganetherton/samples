<?php
namespace ValuePad\Core\Amc\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Amc\Entities\License;
use ValuePad\Core\Amc\Services\LicenseService;

class LicenseNumberAvailable extends AbstractRule
{
    /**
     * @var LicenseService
     */
    private $licenseService;

    /**
     * @var License
     */
    private $ignoreLicense;

    public function __construct(LicenseService $licenseService, License $license = null)
    {
        $this->licenseService = $licenseService;
        $this->ignoreLicense = $license;

        $this->setIdentifier('already-taken');
        $this->setMessage('The license number is taken by another AMC');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        list($number, $state) = $value->extract();

        if (empty($number)) {
            return null;
        }

        if ($this->ignoreLicense
            && $this->ignoreLicense->getNumber() === $number
            && $this->ignoreLicense->getState()->getCode() === $state
        ){
            return null;
        }

        if ($this->licenseService->existsWithNumberInState($number, $state)){
            return $this->getError();
        }

        return null;
    }
}
