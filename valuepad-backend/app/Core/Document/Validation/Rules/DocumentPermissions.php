<?php
namespace ValuePad\Core\Document\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Persistables\Identifier;
use ValuePad\Core\Document\Persistables\Identifiers;
use ValuePad\Core\Document\Services\DocumentService;
use ValuePad\Core\Document\Interfaces\DocumentPreferenceInterface;
use ValuePad\Core\Support\Service\ContainerInterface;
use DateTime;

class DocumentPermissions extends AbstractRule
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DocumentPreferenceInterface
     */
    private $preference;

	/**
	 * @var Document[]
	 */
	private $trustedDocuments = [];

    /**
     * @param ContainerInterface $container
	 * @param Document[] $trustedDocuments
     */
    public function __construct(ContainerInterface $container, $trustedDocuments = [])
    {
        $this->container = $container;
		$this->trustedDocuments = $trustedDocuments;
        $this->preference = $container->get(DocumentPreferenceInterface::class);

        $this->setIdentifier('permissions');
        $this->setMessage('Unable to access one or several documents with the provided ID(s).');
    }

    /**
     * @param Identifier|Identifiers $identifiers
     * @return Error|null
     */
    public function check($identifiers)
    {
        if (!$identifiers instanceof Identifiers) {
            $identifiers = [$identifiers];
        }

        foreach ($identifiers as $identifier) {
            if (!$this->checkSingle($identifier)) {
                return $this->getError();
            }
        }

        return null;
    }

    /**
     * @param Identifier $identifier
     * @return bool
     */
    private function checkSingle(Identifier $identifier)
    {
        /**
         * @var DocumentService $documentService
         */
        $documentService = $this->container->get(DocumentService::class);

        $document = $documentService->get($identifier->getId());

        return $this->grantsWithTrusted($document) || $this->grantsWithToken($identifier, $document);
    }

	/**
	 * @param Document $document
	 * @return bool
	 */
	private function grantsWithTrusted(Document $document)
	{
		foreach ($this->trustedDocuments as $trustedDocument){
			if ($trustedDocument->getId() == $document->getId()){
				return true;
			}
		}

		return false;
	}

    /**
     * @param Identifier $identifier
     * @param Document $document
     * @return bool
     */
    private function grantsWithToken(Identifier $identifier, Document $document)
    {
        $token = $identifier->getToken();

        if (! $token) {
            return false;
        }

        $tokenExpiresAt = clone $document->getUploadedAt();
        $tokenExpiresAt->modify('+' . $this->preference->getLifetime() . ' minutes');

        return $token === $document->getToken() && $tokenExpiresAt > new DateTime();
    }
}
