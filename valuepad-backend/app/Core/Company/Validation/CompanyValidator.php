<?php
namespace ValuePad\Core\Company\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Appraiser\Validation\Definers\AchDefiner;
use ValuePad\Core\Appraiser\Validation\Definers\EoDefiner;
use ValuePad\Core\Appraiser\Validation\Rules\Tin;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Services\BranchService;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Validation\Rules\UniqueTaxId;
use ValuePad\Core\Document\Validation\DocumentInflator;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Definer\LocationDefiner;
use ValuePad\Core\Location\Validation\Rules\Zip;
use ValuePad\Core\Shared\Validation\Rules\Phone;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\User\Validation\Inflators\EmailInflator;
use ValuePad\Core\User\Validation\Inflators\FirstNameInflator;
use ValuePad\Core\User\Validation\Inflators\LastNameInflator;

class CompanyValidator extends AbstractThrowableValidator
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var CompanyService
     */
    private $companyService;

    /**
     * @var Company
     */
    private $currentCompany;

    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @var BranchService
     */
    private $branchService;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->companyService = $container->get(CompanyService::class);
        $this->stateService = $container->get(StateService::class);
        $this->branchService = $container->get(BranchService::class);
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('name', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank())
                ->addRule(new Length(1, 255));
        });

        $binder->bind('type', function(Property $property){
            $property->addRule(new Obligate());
        });

        $binder->bind('taxId', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Tin(Tin::TAX_ONLY))
                ->addRule(new UniqueTaxId($this->companyService, $this->currentCompany, $this->branchService));
        });

        $inflator = new DocumentInflator($this->container);

        if ($w9 = object_take($this->currentCompany, 'w9')){
            $inflator->setTrustedDocuments([$w9]);
        }

        $inflator->setRequired(true);

        $binder->bind('w9', $inflator);

        $binder->bind('firstName', new FirstNameInflator());
        $binder->bind('lastName', new LastNameInflator());
        $binder->bind('email', new EmailInflator());

        $binder->bind('phone', function (Property $property) {
            $property
                ->addRule(new Obligate())
                ->addRule(new Phone());
        });

        $binder->bind('fax', function (Property $property) {
            $property
                ->addRule(new Phone());
        });

        (new LocationDefiner($this->stateService))->define($binder);

        $binder->bind('assignmentZip', function (Property $property) {
            $property->addRule(new Obligate())
                ->addRule(new Zip());
        });

        (new AchDefiner())->setNamespace('ach')->define($binder);

        $eo = new EoDefiner($this->container);

        $eo->setOnlyWhenSpecified(true);

        if ($eoDocument = object_take($this->currentCompany, 'eo.document')){
            $eo->setTrustedDocument($eoDocument);
        }

        $eo->define($binder);
    }

    /**
     * @param Company $company
     * @return $this
     */
    public function setCurrentCompany(Company $company)
    {
        $this->currentCompany = $company;
        return $this;
    }
}
