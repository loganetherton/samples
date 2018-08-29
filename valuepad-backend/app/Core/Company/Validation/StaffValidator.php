<?php
namespace ValuePad\Core\Company\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Email;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Validation\Rules\BranchExists;
use ValuePad\Core\Shared\Validation\Rules\Phone;
use ValuePad\Core\Support\Service\ContainerInterface;

class StaffValidator extends AbstractThrowableValidator
{
    /**
     * @var Company
     */
    protected $company;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @param Company $company
     */
    public function __construct(ContainerInterface $container, $company)
    {
        $this->container = $container;
        $this->company = $company;
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('email', function (Property $property) {
            $property
                ->addRule(new Blank())
                ->addRule(new Length(1, 255))
                ->addRule(new Email());
        });

        $binder->bind('phone', function(Property $property){
            $property
                ->addRule(new Phone());
        });

        $binder->bind('branch', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new BranchExists($this->container->get(CompanyService::class), $this->company));
        });
    }
}
