<?php
namespace ValuePad\Core\Company\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Asc\Services\AscService;
use ValuePad\Core\Asc\Validation\Rules\AscAppraiserExists;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Services\BranchService;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Services\InvitationService;
use ValuePad\Core\Company\Validation\Rules\AppraiserNotInCompany;
use ValuePad\Core\Company\Validation\Rules\AppraiserNotInvitedToCompany;
use ValuePad\Core\Shared\Validation\Rules\Phone;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\User\Validation\Inflators\EmailInflator;

class InvitationValidator extends AbstractThrowableValidator
{
    /**
     * @var AscService
     */
    private $ascService;

    /**
     * @var CompanyService
     */
    private $companyService;

    /**
     * @var BranchService
     */
    private $branchService;

    /**
     * @var InvitationService
     */
    private $invitationService;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->ascService = $container->get(AscService::class);
        $this->companyService = $container->get(CompanyService::class);
        $this->branchService = $container->get(BranchService::class);
        $this->invitationService = $container->get(InvitationService::class);
    }

    /**
     * @param Binder $binder
     */
    protected function define(Binder $binder)
    {
        $binder->bind('ascAppraiser', function (Property $property) {
            $property
                ->addRule(new Obligate())
                ->addRule(new AscAppraiserExists($this->ascService))
                ->addRule(new AppraiserNotInvitedToCompany($this->invitationService, $this->branchService, $this->company))
                ->addRule(new AppraiserNotInCompany($this->companyService, $this->company));
        });

        $binder->bind('email', new EmailInflator());

        $binder->bind('phone', function (Property $property) {
            $property
                ->addRule(new Obligate())
                ->addRule(new Phone());
        });
    }

    /**
     * @param Company $company
     * @return $this
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;
        return $this;
    }
}
