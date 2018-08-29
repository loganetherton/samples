<?php
namespace ValuePad\Core\Appraisal\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;

use ValuePad\Core\Document\Persistables\Identifiers;
use ValuePad\Core\Document\Services\DocumentService as SourceService;


class UniquePrimaryDocumentFormats extends AbstractRule
{
    /**
     * @var SourceService
     */
    private $sourceService;

    /**
     * @param SourceService $sourceService
     */
    public function __construct(SourceService $sourceService)
    {
        $this->sourceService = $sourceService;

        $this
            ->setMessage('The documents must have different formats.')
            ->setIdentifier('unique');
    }

    /**
     * @param mixed|Value|Identifiers $value
     * @return Error|null
     */
    public function check($value)
    {
        $documents = $this->sourceService->getAllSelected($value->getIds());

        $formats = [];

        foreach ($documents as $document){
            if (in_array((string) $document->getFormat(), $formats)){
                return $this->getError();
            }

            $formats[] = (string) $document->getFormat();
        }

        return null;
    }
}
