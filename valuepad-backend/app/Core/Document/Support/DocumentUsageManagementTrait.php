<?php
namespace ValuePad\Core\Document\Support;

use ValuePad\Core\Document\Entities\Document;

/**
 *
 *
 */
trait DocumentUsageManagementTrait
{
    /**
     * @param Document|null $oldDocument
     * @param Document|null $newDocument
     */
    protected function handleUsageOfOneDocument($oldDocument, $newDocument)
    {
        (new DocumentUsageManagement())->handleSingle($oldDocument, $newDocument);
    }

    /**
     *
     * @param Document[]|null $oldDocuments
     * @param Document[]|Document $newDocuments
     */
    protected function handleUsageOfMultipleDocuments($oldDocuments, $newDocuments)
    {
        (new DocumentUsageManagement())->handleMultiple($oldDocuments, $newDocuments);
    }
}
