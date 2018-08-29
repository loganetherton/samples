<?php
namespace ValuePad\Core\Company\Validation;

use Ascope\Libraries\Converter\Transferer\Transferer;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Appraiser\Entities\Eo;
use ValuePad\Core\Appraiser\Persistables\EoPersistable;
use ValuePad\Core\Appraiser\Validation\Definers\EoDefiner;
use ValuePad\Core\Appraiser\Validation\Rules\Tin;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Persistables\BranchPersistable;
use ValuePad\Core\Company\Services\BranchService;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Validation\Rules\UniqueTaxId;
use ValuePad\Core\Document\Persistables\Identifier;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Definer\LocationDefiner;
use ValuePad\Core\Location\Validation\Rules\Zip;
use ValuePad\Core\Support\Service\ContainerInterface;

class BranchValidator extends AbstractThrowableValidator
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
     * @var BranchService
     */
    private $branchService;

    /**
     * @var Company
     */
    private $currentCompany;

    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @var Branch
     */
    private $currentBranch;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->companyService = $container->get(CompanyService::class);
        $this->branchService = $container->get(BranchService::class);
        $this->stateService = $container->get(StateService::class);
    }

    /**
     * @param Binder $binder
     */
    protected function define(Binder $binder)
    {
        $binder->bind('name', function (Property $property) {
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank())
                ->addRule(new Length(1, 255));
        });

        $binder->bind('taxId', function (Property $property) {
            $property
                ->addRule(new Tin(Tin::TAX_ONLY))
                ->addRule(new UniqueTaxId(
                    $this->companyService, $this->currentCompany, $this->branchService, $this->currentBranch
                ));
        });

        (new LocationDefiner($this->stateService))->define($binder);

        $binder->bind('assignmentZip', function (Property $property) {
            $property
                ->addRule(new Obligate())
                ->addRule(new Zip());
        });

        $eo = new EoDefiner($this->container);

        $eo->setOnlyWhenSpecified(true);

        if ($eoDocument = object_take($this->currentBranch, 'eo.document')) {
            $eo->setTrustedDocument($eoDocument);
        }

        $eo->define($binder);
    }

    /**
     * @param BranchPersistable $source
     * @param Branch $branch
     */
    public function validateWithBranch(BranchPersistable $source, Branch $branch)
    {
        $persistable = new BranchPersistable();

        (new Transferer([
            'ignore' => [
                'state',
                'eo',
                'company',
                'staff'
            ]
        ]))->transfer($branch, $persistable);

        $persistable->setState($branch->getState()->getCode());

        if ($branch->getEo()) {
            $eoPersistable = new EoPersistable();

            (new Transferer([
                'ignore' => [
                    'document'
                ]
            ]))->transfer($branch->getEo(), $eoPersistable);

            if ($document = $branch->getEo()->getDocument()) {
                $eoPersistable->setDocument(new Identifier($document->getId()));
            }

            $persistable->setEo($eoPersistable);
        }

        (new Transferer([
            'nullable' => $this->getForcedProperties()
        ]))->transfer($source, $persistable);

        $this->validate($persistable);
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

    /**
     * @param Branch $branch
     * @return $this
     */
    public function setCurrentBranch(Branch $branch)
    {
        $this->currentBranch = $branch;
        return $this;
    }
}
