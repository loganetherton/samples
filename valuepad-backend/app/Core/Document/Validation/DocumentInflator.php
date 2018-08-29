<?php
namespace ValuePad\Core\Document\Validation;

use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Services\DocumentService;
use ValuePad\Core\Document\Validation\Rules\DocumentExists;
use ValuePad\Core\Document\Validation\Rules\DocumentPermissions;
use ValuePad\Core\Support\Service\ContainerInterface;

class DocumentInflator
{
	/**
	 * @var ContainerInterface $container
	 */
	protected $container;

	/**
	 * @var bool
	 */
	private $isRequired = false;

	/**
	 * @var Document[]
	 */
	private $trustedDocuments = [];

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function setRequired($bool)
	{
		$this->isRequired = $bool;
		return $this;
	}

	/**
	 * @param Document[] $documents
	 * @return $this
	 */
	public function setTrustedDocuments($documents)
	{
		$this->trustedDocuments = $documents;
		return $this;
	}

	/**
	 * @param Property $property
	 */
	public function __invoke(Property $property)
	{
		/**
		 * @var DocumentService $documentService
		 */
		$documentService = $this->container->get(DocumentService::class);

		if ($this->isRequired){
			$property->addRule(new Obligate());
		}

		$property
			->addRule(new DocumentExists($documentService))
			->addRule(new DocumentPermissions($this->container, $this->trustedDocuments));
	}
}
