<?php
namespace ValuePad\Core\Amc\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\License;
use ValuePad\Core\Amc\Services\AmcService;

class LicenseStateUnique extends AbstractRule
{
    /**
     * @var AmcService
     */
    private $amcService;

    /**
     * @var Amc
     */
    private $currentAmc;

    /**
     * @var License
     */
    private $ignoreLicense;

    /**
     * @param AmcService $amcService
     * @param Amc $amc
     * @param License $ignoreLicense
     */
    public function __construct(AmcService $amcService, Amc $amc, License $ignoreLicense = null)
    {
        $this->amcService = $amcService;
        $this->currentAmc = $amc;
        $this->ignoreLicense = $ignoreLicense;

        $this->setIdentifier('unique');
        $this->setMessage('The license has been added already for the specified state.');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        if ($this->ignoreLicense && $this->ignoreLicense->getState()->getCode() === $value){
            return null;
        }

        if ($this->amcService->hasLicenseInState($this->currentAmc->getId(), $value)){
            return $this->getError();
        }

        return null;
    }
}
