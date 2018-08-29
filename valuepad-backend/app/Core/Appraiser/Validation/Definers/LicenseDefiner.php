<?php
namespace ValuePad\Core\Appraiser\Validation\Definers;

use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Callback;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\NotClearable;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Appraiser\Validation\Rules\LicenseNumberTaken;
use ValuePad\Core\Appraiser\Validation\Rules\StateUnique;
use ValuePad\Core\Assignee\Validation\Inflators\CoverageInflator;
use ValuePad\Core\Assignee\Validation\Rules\WalkWithState;
use ValuePad\Core\Asc\Services\AscService;
use ValuePad\Core\Asc\Validation\Rules\LicenseNumberExists;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Validation\DocumentInflator;
use ValuePad\Core\Location\Services\CountyService;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Rules\StateExists;
use DateTime;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Support\Service\ContainerInterface;

class LicenseDefiner
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var StateService
	 */
	private $stateService;

	/**
	 * @var AscService
	 */
	private $ascService;

	/**
	 * @var AppraiserService
	 */
	private $appraiserService;

	/**
	 * @var CountyService
	 */
	private $countyService;

	/**
	 * @var string
	 */
	private $namespace = '';

	/**
	 * @var bool
	 */
	private $bypassValidateExistence = false;

	/**
	 * @var Appraiser
	 */
	private $currentAppraiser;

	/**
	 * @var SourceHandlerInterface
	 */
	private $origin;

	/**
	 * @var Document
	 */
	private $trustedDocument;

	/**
	 * @var bool
	 */
	private $validateExpiresAt = true;

	/**
	 * @var EnvironmentInterface
	 */
	private $environment;

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->stateService = $container->get(StateService::class);
		$this->ascService = $container->get(AscService::class);
		$this->appraiserService = $container->get(AppraiserService::class);
		$this->countyService = $container->get(CountyService::class);
		$this->environment = $container->get(EnvironmentInterface::class);
	}

	/**
	 * @param Binder $binder
	 */
	public function defineOnCreate(Binder $binder)
	{
		$binder->bind($this->namespace.'number', function (Property $property) {
			$property
				->addRule(new Obligate())
				->addRule(new Blank());
		});

		$binder->bind($this->namespace.'state', function (Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Length(2, 2))
				->addRule(new StateExists($this->stateService));

			if ($this->currentAppraiser){
				$property->addRule(new StateUnique($this->appraiserService, $this->currentAppraiser));
			}
		});

		$binder->bind($this->namespace.'number', [$this->namespace.'number', $this->namespace.'state'],
			function (Property $property){

				if ($this->bypassValidateExistence === false){
					$property->addRule(new LicenseNumberExists($this->ascService));
				}

				$property->addRule(new LicenseNumberTaken($this->appraiserService));
			});


		$this->define($binder);
	}

	/**
	 * @param Binder $binder
	 */
	public function defineOnUpdate(Binder $binder)
	{
		$binder->bind($this->namespace.'number', function (Property $property) {
			$property->addRule($this->readOnly(function(){
				return !$this->origin->hasProperty($this->namespace.'number');
			}));
		});

		$binder->bind($this->namespace.'state', function (Property $property){
			$property->addRule($this->readOnly(function(){
				return !$this->origin->hasProperty($this->namespace.'state');
			}));
		});

		$binder->bind($this->namespace.'isFhaApproved', function(Property $property){
			$property
				->addRule(new NotClearable());
		});

		$binder->bind($this->namespace.'isCommercial', function(Property $property){
			$property
				->addRule(new NotClearable());
		});

		$this->define($binder);
	}

	/**
	 * @param Binder $binder
	 */
	private function define(Binder $binder)
	{
		$binder->bind($this->namespace.'expiresAt', function (Property $property){

			if (!$this->environment->isRelaxed()){
				$property->addRule(new Obligate());
			}

			if ($this->validateExpiresAt){
				$property->addRule(new Greater(new DateTime()));
			}
		});

		$binder->bind($this->namespace.'certifications', function (Property $property) {
			$property
				->addRule(new Obligate())
				->addRule(new Blank());
		});

		$inflator = new DocumentInflator($this->container);

		if ($this->trustedDocument){
			$inflator->setTrustedDocuments([$this->trustedDocument]);
		}

		$binder->bind($this->namespace.'document', $inflator);

		$binder->bind($this->namespace.'coverages', [$this->namespace.'coverages', $this->namespace.'state'],
			function(Property $property){
				$property
					->addRule(new WalkWithState(
						new CoverageInflator($this->stateService, $this->countyService)
					));

			});
	}

	/**
	 * @param callable $callback
	 * @return AbstractRule
	 */
	private function readOnly(callable $callback)
	{
		return (new Callback($callback))
			->setIdentifier('read-only')
			->setMessage('The property cannot be updated.');
	}

	/**
	 * @param bool $flag
	 * @return $this
	 */
	public function setBypassValidateExistence($flag)
	{
		$this->bypassValidateExistence = $flag;

		return $this;
	}

	/**
	 * @param string $namespace
	 * @return $this
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace.'.';
		return $this;
	}

	/**
	 * @param Appraiser $appraiser
	 * @return $this
	 */
	public function setCurrentAppraiser(Appraiser $appraiser)
	{
		$this->currentAppraiser = $appraiser;
		return $this;
	}

	/**
	 * @param Document $document
	 * @return $this
	 */
	public function setTrustedDocument(Document $document = null)
	{
		$this->trustedDocument = $document;
		return $this;
	}

	/**
	 * @param SourceHandlerInterface $source
	 * @return $this
	 */
	public function setOrigin(SourceHandlerInterface $source)
	{
		$this->origin = $source;
		return $this;
	}

	/**
	 * @param bool $flag
	 * @return $this
	 */
	public function setValidateExpiresAt($flag)
	{
		$this->validateExpiresAt = $flag;
		return $this;
	}
}
