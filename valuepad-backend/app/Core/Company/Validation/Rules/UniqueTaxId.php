<?php
namespace ValuePad\Core\Company\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Services\BranchService;
use ValuePad\Core\Company\Services\CompanyService;

class UniqueTaxId extends AbstractRule
{
    /**
     * @var CompanyService
     */
    private $companyService;

    /**
     * @var Company
     */
    private $currentCompany;

    /**
     * @var BranchService
     */
    private $branchService;

    /**
     * @var Branch
     */
    private $currentBranch;

    /**
     * @param CompanyService $companyService
     * @param Company $currentCompany
     * @param BranchService $branchService
     * @param Branch $currentBranch
     */
    public function __construct(
        CompanyService $companyService,
        Company $currentCompany = null,
        BranchService $branchService = null,
        Branch $currentBranch = null
    ) {
        $this->companyService = $companyService;
        $this->currentCompany = $currentCompany;
        $this->branchService = $branchService;
        $this->currentBranch = $currentBranch;

        $this->setIdentifier('unique');
        $this->setMessage('The provided tax id is already registered.');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        if ($this->currentCompany && $this->currentCompany->getTaxId() === $value){
            return null;
        }

        if ($this->companyService->existsWithTaxId($value)){
            return $this->getError();
        }

        if ($this->currentBranch && $this->currentBranch->getTaxId() === $value) {
            return null;
        }

        $companyId = $this->currentCompany ? $this->currentCompany->getId() : null;

        if ($this->branchService->existsWithTaxId($value, $companyId)) {
            return $this->getError();
        }

        return null;
    }
}
