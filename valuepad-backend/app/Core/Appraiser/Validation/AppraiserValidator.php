<?php
namespace ValuePad\Core\Appraiser\Validation;

use Ascope\Libraries\Converter\Transferer\Transferer;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Source\ObjectSourceHandler;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Persistables\AppraiserPersistable;
use ValuePad\Core\Shared\Persistables\AvailabilityPersistable;
use ValuePad\Core\Appraiser\Persistables\LicensePersistable;
use ValuePad\Core\Appraiser\Persistables\QualificationsPersistable;
use ValuePad\Core\Shared\Validation\Definers\AvailabilityDefiner;
use ValuePad\Core\Appraiser\Validation\Definers\EoDefiner;
use ValuePad\Core\Appraiser\Validation\Definers\LicenseDefiner;
use ValuePad\Core\Appraiser\Validation\Rules\CertifiedDate;
use ValuePad\Core\Appraiser\Validation\Rules\Tin;
use ValuePad\Core\Asc\Services\AscService;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Persistables\Identifier;
use ValuePad\Core\Document\Persistables\Identifiers;
use ValuePad\Core\Document\Validation\DocumentInflator;
use ValuePad\Core\Language\Entities\Language;
use ValuePad\Core\Language\Services\LanguageService;
use ValuePad\Core\Language\Validation\Rules\LanguageExists;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Definer\LocationDefiner;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\Shared\Validation\Rules\Phone;
use ValuePad\Core\User\Services\UserService;
use ValuePad\Core\User\Validation\Inflators\EmailInflator;
use ValuePad\Core\User\Validation\Inflators\FirstNameInflator;
use ValuePad\Core\User\Validation\Inflators\LastNameInflator;
use ValuePad\Core\User\Validation\Inflators\PasswordInflator;
use ValuePad\Core\User\Validation\Inflators\UsernameInflator;

/**
 *
 *
 */
class AppraiserValidator extends AbstractThrowableValidator
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var EnvironmentInterface $environment
	 */
	private $environment;

	/**
     * @var StateService
     */
    private $stateService;

    /**
     * @var AscService
     */
    private $ascService;

    /**
     * @var LanguageService
     */
    private $languageService;

	/**
	 * @var bool
	 */
	private $validateEoExpiresAt = true;

	/**
	 * @var bool
	 */
	private $validatePrimaryLicenseExpiresAt = true;

	/**
	 * @var bool
	 */
	private $isUpdate = false;

	/**
	 * @var bool
	 */
	private $bypassValidatePrimaryLicenseExistence = false;

	/**
	 * @var SourceHandlerInterface|ObjectSourceHandler
	 */
	private $origin;

	/**
	 * @var Document
	 */
	private $trustedDocument;

	/**
	 * @var Appraiser
	 */
	private $currentAppraiser;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
		$this->container = $container;

		$this->userService = $container->get(UserService::class);
		$this->environment = $container->get(EnvironmentInterface::class);
        $this->stateService = $container->get(StateService::class);
        $this->ascService = $container->get(AscService::class);
        $this->languageService = $container->get(LanguageService::class);
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
		$binder->bind('firstName', new FirstNameInflator());
		$binder->bind('lastName', new LastNameInflator());
		$binder->bind('username', new UsernameInflator($this->userService, $this->environment, $this->currentAppraiser));
		$binder->bind('password', new PasswordInflator($this->environment));
		$binder->bind('email', new EmailInflator());

		$constraints = new Constraints();

		if ($this->currentAppraiser){
			$constraints->setAppraiser($this->currentAppraiser);
		}

        (new AvailabilityDefiner())
            ->setNamespace('availability')
            ->define($binder);

		$this->definePrimaryLicense($binder);

		$binder->bind('companyName', function (Property $property) {
			$property
				->addRule(new Obligate())
				->addRule(new Blank());
		});

		if (!$this->isRelaxed()){

			$binder->bind('businessTypes', function (Property $property) {
				$property
					->addRule(new Obligate())
					->addRule(new Blank());
			});

			$binder->bind('companyType', function (Property $property) {
				$property->addRule(new Obligate());
			});
		}

		$binder->bind('taxIdentificationNumber', function (Property $property) {
			$property->addRule(new Tin());

			if (!$this->isRelaxed()){
				$property->addRule(new Obligate());
			}
		});

        $binder->bind('languages', function (Property $property) {
            $property
				->addRule(new Obligate())
				->addRule(new Blank())
                ->addRule(new LanguageExists($this->languageService));
        });

        (new LocationDefiner($this->stateService))->define($binder);
        (new LocationDefiner($this->stateService))->setPrefix('assignment')->define($binder);

        $binder->bind('phone', function (Property $property) {
            $property
				->addRule(new Obligate())
                ->addRule(new Phone());
        });

        $binder->bind('cell', function (Property $property) {
            $property
				->addRule(new Obligate())
                ->addRule(new Phone());
        });

        $binder->bind('fax', function (Property $property) {
            $property
				->addRule(new Phone());
        });

		$inflator = new DocumentInflator($this->container);

		if ($w9 = object_take($this->currentAppraiser, 'w9')){
			$inflator->setTrustedDocuments([$w9]);
		}

		if (!$this->isRelaxed()){
			$inflator->setRequired(true);
		}

		$binder->bind('w9', $inflator);

		$inflator = new DocumentInflator($this->container);

		if ($resume = object_take($this->currentAppraiser, 'qualifications.resume')){
			$inflator->setTrustedDocuments([$resume]);
		}

        $binder->bind('qualifications.resume', $inflator);

		$binder->bind('qualifications.yearsLicensed', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Greater(0));
		});

		$binder->bind('qualifications.certifiedAt', function(Property $property){
			$property->addRule(new CertifiedDate());
		});

		if (!$this->isRelaxed()){
			$binder->bind('qualifications.certifiedAt', function(Property $property){
				$property->addRule(new Obligate());
			})->when($constraints->are([Constraints::PRIMARY_LICENSE_CERTIFICATIONS_CONTAIN_RESIDENTIAL_OR_GENERAL]));
		}

		$binder->bind('qualifications.commercialQualified', function(Property $property){
			$property->addRule(new Obligate());
		})->when($constraints->are([Constraints::PRIMARY_LICENSE_CERTIFICATIONS_CONTAIN_GENERAL]));


		$binder->bind('qualifications.commercialExpertise', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank());
		})->when($constraints->are([Constraints::COMMERCIAL_QUALIFIED_EQUALS_TRUE]));

		$binder->bind('qualifications.otherCommercialExpertise', function(Property $property){
			$property
				->addRule(new Blank());
		});

		$binder->bind('qualifications.otherCommercialExpertise', function(Property $property){
			$property
				->addRule(new Obligate());
		})->when($constraints->are([Constraints::COMMERCIAL_EXPERTISE_CONTAIN_OTHER]));

		$binder->bind('qualifications.newConstructionExperienceInYears', function(Property $property){
			$property->addRule(new Greater(0));
		});

		$binder->bind('qualifications.numberOfNewConstructionCompleted', function(Property $property){
			$property->addRule(new Greater(0));
		});

		foreach ([
			'qualifications.newConstructionExperienceInYears',
			'qualifications.numberOfNewConstructionCompleted',
			'qualifications.isNewConstructionCourseCompleted',
			'qualifications.isFamiliarWithFullScopeInNewConstruction'
		] as $field){
			$binder->bind($field, function(Property $property){
				$property
					->addRule(new Obligate());
			})
				->when(function(SourceHandlerInterface $source, ErrorsThrowableCollection $errors) use ($constraints){
					return $constraints->newConstructionQualifiedEqualsTrue($source, $errors);
				});
		}

		$eo = new EoDefiner($this->container);

        if ($eoDocument = object_take($this->currentAppraiser, 'eo.document')){
            $eo->setTrustedDocument($eoDocument);
        }

        $eo->define($binder);

        $eo->setRelaxed($this->isRelaxed());
        $eo->setBypassExpiresAt($this->validateEoExpiresAt === false);

		for ($i = 1; $i <= 7; $i++){
			$binder->bind('eo.question'.$i, function (Property $property) {
				$property
					->addRule(new Obligate());
			});

			$binder->bind('eo.question'.$i.'Explanation', function(Property $property){
				$property
					->addRule(new Blank())
					->addRule(new Length(1, 255));
			});

			$binder->bind('eo.question'.$i.'Explanation', function(Property $property){
				$property
					->addRule(new Obligate());
			})->when(function(SourceHandlerInterface $source, ErrorsThrowableCollection $errors) use ($constraints, $i){
				return $constraints->questionEqualsTrue($source, $errors, $i);
			});
		}

		$inflator = new DocumentInflator($this->container);

		if ($question1Document = object_take($this->currentAppraiser, 'eo.question1Document')){
			$inflator->setTrustedDocuments([$question1Document]);
		}

		$binder->bind('eo.question1Document', $inflator);

		$binder->bind('eo.question1Document', function(Property $property){
			$property->addRule(new Obligate());
		})->when($constraints->are([Constraints::QUESTION_1_EQUALS_TRUE]));

		$inflator = (new DocumentInflator($this->container))
			->setTrustedDocuments(object_take($this->currentAppraiser, 'sampleReports', []));

        $binder->bind('sampleReports', $inflator);

		$binder->bind('signature', function(Property $property){
			$property->addRule(new Blank());

			if (!$this->isRelaxed()){
				$property->addRule(new Obligate());
			}
		});

		$binder->bind('signedAt', function(Property $property){
			$property
				->addRule(new Obligate());
		});
    }

	/**
	 * @param Binder $binder
	 */
	private function definePrimaryLicense(Binder $binder)
	{
		$definer = new LicenseDefiner($this->container);
		$definer->setNamespace('qualifications.primaryLicense');

		if ($this->isUpdate){
			$definer
				->setValidateExpiresAt($this->validatePrimaryLicenseExpiresAt)
				->setTrustedDocument($this->trustedDocument)
				->setOrigin($this->origin)
				->defineOnUpdate($binder);
		} else {
			$definer
				->setBypassValidateExistence($this->bypassValidatePrimaryLicenseExistence)
				->defineOnCreate($binder);
		}
	}

    /**
     * @return AscService
     */
    protected function getAscService()
    {
        return $this->ascService;
    }

	/**
	 * @param AppraiserPersistable $persistable
	 * @param Appraiser $appraiser
	 */
	public function validateSoftlyWithAppraiser(AppraiserPersistable $persistable, Appraiser $appraiser)
	{
		$this->currentAppraiser = $appraiser;
		$this->origin = $origin = new ObjectSourceHandler($persistable, $this->getForcedProperties());
		$this->isUpdate = true;
		$this->trustedDocument = $appraiser->getQualifications()->getPrimaryLicense()->getDocument();

		$config = [
			'source' => [
				'availability.isOnVacation' => [
					'availability.from',
					'availability.to'
				],
				'availability.from' => [
					'availability.to'
				],
				'qualifications.primaryLicense.certifications' => [
					'qualifications.certifiedAt',
					'qualifications.commercialQualified'
				],

				'qualifications.commercialQualified' => [
					'qualifications.commercialExpertise'
				],
				'qualifications.commercialExpertise' => [
					'qualifications.otherCommercialExpertise'
				],
				'qualifications.newConstructionQualified' => [
					'qualifications.newConstructionExperienceInYears',
					'qualifications.numberOfNewConstructionCompleted',
					'qualifications.isNewConstructionCourseCompleted',
					'qualifications.isFamiliarWithFullScopeInNewConstruction'
				]
			],
			'modifier' => function($path, $value){
				if ($path == 'eo.question1Document'){
					return new Identifier($value->getId());
				}

				return $value;
			},
			'resolver' => function($path){
				if ($path === 'qualifications.primaryLicense'){
					return new LicensePersistable();
				}

				if ($path == 'availability'){
					return new AvailabilityPersistable();
				}

				if ($path === 'qualifications'){
					return new QualificationsPersistable();
				}

				return null;
			}
		];

		for ($i = 1; $i <= 7; $i++){

			$config['eo.question'.$i][] = 'eo.question'.$i.'Explanation';
		}

		$config['eo.question1'][] = 'eo.question1Document';

		$this->preparePersistableForSoftValidation($config, $persistable, $appraiser, $origin);

		$this->setForcedProperties($origin->getForcedProperties());

		$this->validate($persistable, true);
	}


	private function preparePersistableForSoftValidation(
		array $config,
		AppraiserPersistable $persistable,
		Appraiser $appraiser,
		ObjectSourceHandler $origin
	){

		$modifier = $config['modifier'];
		$resolver = $config['resolver'];

		$source = [];

		foreach ($config['source'] as $key => $paths){
			$source[$key] = $paths;
			foreach ($paths as $path){
				$source[$path][] = $key;
			}
		}

		foreach ($source as $key => $paths){
			if ($origin->hasProperty($key)){
				foreach ($paths as $path){

					if (!$origin->hasProperty($path)){
						$value = object_take($appraiser, $path);

						if ($value === null){
							$origin->addForcedProperty($path);
						} else {
							$parts = explode('.', $path);
							$setter = array_pop($parts);
							$setter = make_setter($setter);
							$target = $persistable;
							foreach ($parts as $piece){
								$getter = make_getter($piece);

								$newTarget = call_user_func([$target, $getter]);

								if ($newTarget === null){
									$newTarget = $resolver(implode('.', $parts));
									call_user_func([$target, make_setter($piece)], $newTarget);
								}

								$target = $newTarget;
							}
							call_user_func([$target, $setter], call_user_func($modifier, $path, $value));
						}
					}
				}
			}
		}
	}

	/**
	 * This will allow us to securely validate the data when updating the entity partially.
	 *
	 * The logic is the following:
	 * 1. We populate a new empty persistable object with the data taken from the entity itself
	 * 2. We override the data in the new persistable object with the received data in the source object
	 * 3. We validate the new persistable object instead of the source object.
	 *
	 * @param AppraiserPersistable $source
	 * @param Appraiser $appraiser
	 * @throws ErrorsThrowableCollection
	 */
	public function validateWithAppraiser(AppraiserPersistable $source, Appraiser $appraiser)
	{
		$this->currentAppraiser = $appraiser;

		if (object_take($source, 'eo.expiresAt') === null){
			$this->validateEoExpiresAt = false;
		}

		if (object_take($source, 'qualifications.primaryLicense.expiresAt') === null){
			$this->validatePrimaryLicenseExpiresAt = false;
		}

		$this->isUpdate = true;

		$this->origin = $this->getSourceHandler($source);
		$this->trustedDocument = $appraiser->getQualifications()->getPrimaryLicense()->getDocument();

		$persistable = new AppraiserPersistable();

		/**
		 * @var AppraiserPersistable $persistable
		 */
		$persistable = (new Transferer([
			'ignore' => [
				'languages',
				'state',
				'assignmentState',
				'sampleReports',
				'w9',
				'eo.document',
				'eo.question1Document',
				'qualifications.resume',
				'qualifications.primaryLicense.document',
				'qualifications.primaryLicense.coverages',
				'qualifications.primaryLicense.state',

				'qualifications.primaryLicense.appraiser',
				'customers'
			]
		]))->transfer($appraiser, $persistable);

		$persistable->setLanguages(array_map(function(Language $language){
			return $language->getCode();
		}, iterator_to_array($appraiser->getLanguages())));

		if ($state = $appraiser->getState()){
			$persistable->setState($state->getCode());
		}

		if ($assignmentState = $appraiser->getAssignmentState()){
			$persistable->setAssignmentState($assignmentState->getCode());
		}

		$identifiers = new Identifiers();

		foreach ($appraiser->getSampleReports() as $sample){
			$identifiers->add(new Identifier($sample->getId()));
		}

		$persistable->setSampleReports($identifiers);

		if ($w9 = $appraiser->getW9()){
			$persistable->setW9(new Identifier($w9->getId()));
		}

		if ($eoDocument = $appraiser->getEo()->getDocument()){
			$persistable->getEo()->setDocument(new Identifier($eoDocument->getId()));
		}

		if ($question1Document = $appraiser->getEo()->getQuestion1Document()){
			$persistable->getEo()->setQuestion1Document(new Identifier($question1Document->getId()));
		}

		if ($resume = $appraiser->getQualifications()->getResume()){
			$persistable->getQualifications()
				->setResume(new Identifier($resume->getId()));
		}

		$primaryLicense = $appraiser->getQualifications()->getPrimaryLicense();

		if ($primaryLicenseDocument = $primaryLicense->getDocument()){
			$persistable->getQualifications()->getPrimaryLicense()
				->setDocument(new Identifier($primaryLicenseDocument->getId()));
		}

		$persistable->getQualifications()
			->getPrimaryLicense()
			->adaptCoverages($primaryLicense->getCoverages());

		if ($primaryLicenseState = $primaryLicense->getState()){
			$persistable->getQualifications()->getPrimaryLicense()->setState($primaryLicenseState->getCode());
		}

		(new Transferer([
			'ignore' => [
				'sampleReports',
				'qualifications.primaryLicense.coverages'
			],
			'nullable' => $this->getForcedProperties()
		]))->transfer($source, $persistable);

		if (($sampleReports = $source->getSampleReports()) !== null){
			$persistable->setSampleReports($sampleReports);
		}

		if (($coverages = object_take($source, 'qualifications.primaryLicense.coverages')) !== null){
			$persistable->getQualifications()->getPrimaryLicense()->setCoverages($coverages);
		}

		$this->validate($persistable);
	}

	/**
	 * @param bool $flag
	 */
	public function setBypassValidatePrimaryLicenseExistence($flag)
	{
		$this->bypassValidatePrimaryLicenseExistence = $flag;
	}

	/**
	 * @return bool
	 */
	protected function isRelaxed()
	{
		return $this->environment->isRelaxed();
	}
}
