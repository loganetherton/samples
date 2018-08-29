<?php
namespace ValuePad\Core\Company\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Services\CompanyService;

class BranchExists extends AbstractRule
{
    /**
     * @var CompanyService
     */
    private $companyService;

    /**
     * @var Company
     */
    private $company;

    /**
     * @param CompanyService $companyService
     * @param Company $company
     */
    public function __construct(CompanyService $companyService, $company)
    {
        $this->companyService = $companyService;
        $this->company = $company;

        $this->setIdentifier('exists')->setMessage('The provided branch does not belong to the provided company.');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        if (!$this->companyService->hasBranch($this->company->getId(), $value)){
            return $this->getError();
        }

        return null;
    }
}
