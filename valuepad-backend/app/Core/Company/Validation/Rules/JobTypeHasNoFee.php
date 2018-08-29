<?php
namespace ValuePad\Core\Company\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Services\CompanyService;

class JobTypeHasNoFee extends AbstractRule
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
    public function __construct(CompanyService $companyService, Company $company)
    {
        $this->companyService = $companyService;
        $this->company = $company;
    }

    /**
     * @param int $value
     * @return Error|null
     */
    public function check($value)
    {
        if ($this->companyService->hasFeeWithJobType($this->company->getId(), $value)) {
            return $this->getError();
        }

        return null;
    }
}
