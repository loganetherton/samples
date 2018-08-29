<?php
namespace ValuePad\Core\Amc\Validation;
use Ascope\Libraries\Converter\Transferer\Transferer;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use DateTime;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\License;
use ValuePad\Core\Amc\Persistables\LicensePersistable;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Services\LicenseService;
use ValuePad\Core\Amc\Validation\Rules\LicenseNumberAvailable;
use ValuePad\Core\Amc\Validation\Rules\LicenseStateUnique;
use ValuePad\Core\Assignee\Validation\Inflators\CoverageInflator;
use ValuePad\Core\Assignee\Validation\Rules\WalkWithState;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Persistables\Identifier;
use ValuePad\Core\Document\Validation\DocumentInflator;
use ValuePad\Core\Location\Services\CountyService;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Definer\LocationDefiner;
use ValuePad\Core\Location\Validation\Rules\StateExists;
use ValuePad\Core\Shared\Validation\Rules\Phone;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\User\Validation\Inflators\EmailInflator;

class LicenseValidator extends AbstractThrowableValidator
{
    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @var AmcService
     */
    private $amcService;

    /**
     * @var Amc
     */
    private $currentAmc;

    /**
     * @var Document
     */
    private $trustedDocument;

    /**
     * @var CountyService
     */
    private $countyService;

    /**
     * @var LicenseService
     */
    private $licenseService;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var License
     */
    private $currentLicense;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->stateService = $container->get(StateService::class);
        $this->amcService = $container->get(AmcService::class);
        $this->countyService = $container->get(CountyService::class);
        $this->licenseService = $container->get(LicenseService::class);
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('state', function (Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Length(2, 2))
                ->addRule(new StateExists($this->stateService));

            $property->addRule(new LicenseStateUnique($this->amcService, $this->currentAmc, $this->currentLicense));
        });

        $binder->bind('number', ['number', 'state'], function(Property $property){
            $property->addRule(new LicenseNumberAvailable($this->licenseService, $this->currentLicense));
        });

        $binder->bind('expiresAt', function (Property $property){
            $property
                ->addRule(new Greater(new DateTime()));
        });

        $inflator = new DocumentInflator($this->container);

        if ($this->trustedDocument){
            $inflator->setTrustedDocuments([$this->trustedDocument]);
        }

        $binder->bind('document', $inflator);

        $binder->bind('coverages', ['coverages', 'state'],
            function(Property $property){
                $property
                    ->addRule(new WalkWithState(
                        new CoverageInflator($this->stateService, $this->countyService)
                    ));
            });

        $binder->bind('alias.companyName', function (Property $property) {
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank())
                ->addRule(new Length(1, 255));
        })->when(function (SourceHandlerInterface $source) {
            return $source->hasProperty('alias') && $source->getValue('alias') !== null;
        });

        (new LocationDefiner($this->stateService))->setHolder('alias')->define($binder);

        $binder
            ->bind('alias.email', new EmailInflator())
            ->when(function (SourceHandlerInterface $source) {
                return $source->hasProperty('alias') && $source->getValue('alias') !== null;
            });

        $binder->bind('alias.phone', function (Property $property) {
            $property
                ->addRule(new Obligate())
                ->addRule(new Phone());
        })->when(function (SourceHandlerInterface $source) {
            return $source->hasProperty('alias') && $source->getValue('alias') !== null;
        });

        $binder->bind('alias.fax', function (Property $property) {
            $property
                ->addRule(new Obligate())
                ->addRule(new Phone());
        })->when(function (SourceHandlerInterface $source) {
            return $source->hasProperty('alias') && $source->getValue('alias') !== null;
        });
    }

    /**
     * @param LicensePersistable $source
     * @param License $license
     */
    public function validateWithLicense(LicensePersistable $source, License $license)
    {
        $this->trustedDocument = $license->getDocument();
        $this->currentAmc = $license->getAmc();
        $this->currentLicense = $license;

        $persistable = new LicensePersistable();

        (new Transferer([
            'ignore' => [
                'document',
                'coverages',
                'state',
                'amc',
                'alias.state'
            ]
        ]))->transfer($license, $persistable);

        if ($document = $license->getDocument()){
            $persistable->setDocument(new Identifier($document->getId()));
        }

        $persistable->adaptCoverages($license->getCoverages());

        if ($state = $license->getState()){
            $persistable->setState($state->getCode());
        }

        if ($license->getAlias() && ($state = $license->getAlias()->getState())) {
            $persistable->getAlias()->setState($state->getCode());
        }

        (new Transferer([
            'ignore' => [
                'coverages'
            ],
            'nullable' => $this->getForcedProperties()
        ]))->transfer($source, $persistable);

        if ($source->getCoverages() !== null){
            $persistable->setCoverages($source->getCoverages());
        }

        $this->validate($persistable);
    }

    /**
     * @param Amc $amc
     * @return $this
     */
    public function setCurrentAmc(Amc $amc)
    {
        $this->currentAmc = $amc;

        return $this;
    }
}
