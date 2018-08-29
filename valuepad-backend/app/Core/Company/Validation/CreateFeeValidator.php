<?php
namespace ValuePad\Core\Company\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Validation\Rules\JobTypeHasNoFee;
use ValuePad\Core\JobType\Services\JobTypeService;
use ValuePad\Core\JobType\Validation\Rules\JobTypeExists;

class CreateFeeValidator extends AbstractThrowableValidator
{
    /**
     * @var JobTypeService
     */
    private $jobTypeService;

    /**
     * @param JobTypeService $jobTypeService
     * @param CompanyService $companyService
     * @param Company $company
     */
    public function __construct(JobTypeService $jobTypeService, CompanyService $companyService, Company $company)
    {
        $this->jobTypeService = $jobTypeService;
        $this->companyService = $companyService;
        $this->company = $company;
    }

    /**
     * @param Binder $binder
     */
    protected function define(Binder $binder)
    {
        $binder->bind('jobType', function (Property $property) {
            $property
                ->addRule(new Obligate())
                ->addRule(new JobTypeExists($this->jobTypeService))
                ->addRule(new JobTypeHasNoFee($this->companyService, $this->company));
        });

        $binder->bind('amount', function (Property $property) {
            $property
                ->addRule(new Obligate())
                ->addRule(new Greater(0));
        });
    }
}
