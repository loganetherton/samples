<?php
namespace ValuePad\Core\Appraiser\Validation;

use Ascope\Libraries\Converter\Transferer\Transferer;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Source\ObjectSourceHandler;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Entities\License;
use ValuePad\Core\Appraiser\Persistables\LicensePersistable;
use ValuePad\Core\Appraiser\Validation\Definers\LicenseDefiner;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Persistables\Identifier;
use ValuePad\Core\Support\Service\ContainerInterface;

class LicenseValidator extends AbstractThrowableValidator
{
	/**
	 * @var ContainerInterface $container
	 */
	private $container;

	/**
	 * @var bool
	 */
	private $isUpdate = false;

	/**
	 * @var Appraiser
	 */
	private $currentAppraiser;

	/**
	 * @var Document
	 */
	private $trustedDocument;

	/**
	 * @var SourceHandlerInterface
	 */
	private $origin;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
		$this->container = $container;
    }

    /**
     * @param Binder $binder
     */
    protected function define(Binder $binder)
    {
		if ($this->isUpdate){
			(new LicenseDefiner($this->container))
				->setTrustedDocument($this->trustedDocument)
				->setOrigin($this->origin)
				->defineOnUpdate($binder);
		} else{
			(new LicenseDefiner($this->container))
				->setCurrentAppraiser($this->currentAppraiser)
				->defineOnCreate($binder);
		}
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
	 * @param LicensePersistable $source
	 * @param License $license
	 */
	public function validateWithLicense(LicensePersistable $source, License $license)
	{
		$this->trustedDocument = $license->getDocument();
		$this->isUpdate = true;
		$this->origin = new ObjectSourceHandler($source, $this->getForcedProperties());

		$persistable = new LicensePersistable();

		(new Transferer([
			'ignore' => [
				'document',
				'coverages',
				'state',
				'appraiser',
			]
		]))->transfer($license, $persistable);

		if ($document = $license->getDocument()){
			$persistable->setDocument(new Identifier($document->getId()));
		}

		$persistable->adaptCoverages($license->getCoverages());

		if ($state = $license->getState()){
			$persistable->setState($state->getCode());
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
}
