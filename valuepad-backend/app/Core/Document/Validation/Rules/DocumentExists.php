<?php
namespace ValuePad\Core\Document\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use ValuePad\Core\Document\Persistables\Identifier;
use ValuePad\Core\Document\Persistables\Identifiers;
use ValuePad\Core\Document\Services\DocumentService;

/**
 *
 *
 */
class DocumentExists extends AbstractRule
{

    /**
     *
     * @var DocumentService
     */
    private $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;

        $this->setIdentifier('exists');
        $this->setMessage('The document with the provided ID does not exist.');
    }

    /**
     *
     * @param Identifier|Identifiers $value
     * @return Error|null
     */
    public function check($value)
    {
        $ids = $value instanceof Identifiers ? $value->getIds() : $value->getId();

        if (! $this->documentService->exists($ids)) {
            return $this->getError();
        }

        return null;
    }
}
