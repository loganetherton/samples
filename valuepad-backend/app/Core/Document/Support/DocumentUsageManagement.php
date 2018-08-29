<?php
namespace ValuePad\Core\Document\Support;

use ValuePad\Core\Document\Entities\Document;
use Traversable;

class DocumentUsageManagement
{
    /**
     * @param Document|null $oldDocument
     * @param Document|null $newDocument
     */
    public function handleSingle($oldDocument, $newDocument)
    {
        if ($oldDocument === null && $newDocument === null) {
            return;
        }

        if ($oldDocument === null) {
            $newDocument->increaseUsage();

            return;
        }

        if ($newDocument === null) {
            $oldDocument->decreaseUsage();

            return;
        }

        if ($oldDocument->getId() == $newDocument->getId()) {
            return;
        }

        $newDocument->increaseUsage();
        $oldDocument->decreaseUsage();
    }

    /**
     * @param Document[]|null $oldDocuments
     * @param Document[]|Document|null $newDocuments
     */
    public function handleMultiple($oldDocuments, $newDocuments)
    {
        if ($oldDocuments === null) {
            $oldDocuments = [];
        }

        if ($newDocuments === null) {
            $newDocuments = [];
        }

        if (! is_array($newDocuments) && ! $newDocuments instanceof Traversable) {
            $newDocuments = [$newDocuments];
        }

        $oldDocuments = $this->uniqueCollection($oldDocuments);
        $newDocuments = $this->uniqueCollection($newDocuments);

        foreach ($newDocuments as $document) {
            if (!$this->inCollection($document, $oldDocuments)) {
                $document->increaseUsage();
            }
        }

        foreach ($oldDocuments as $document) {
            if (!$this->inCollection($document, $newDocuments)) {
                $document->decreaseUsage();
            }
        }
    }

    /**
     * @param Document[] $collection
     * @return Document[]
     */
    public function uniqueCollection($collection)
    {
        $result = [];
        $ids = [];

        foreach ($collection as $document) {
            if (in_array($document->getId(), $ids)) {
                continue;
            }

            $result[] = $document;
            $ids[] = $document->getId();
        }

        return $result;
    }

    /**
     * @param Document $document
     * @param Document[] $collection
     * @return bool
     */
    private function inCollection(Document $document, $collection)
    {
        foreach ($collection as $item) {
            if ($item->getId() == $document->getId()) {
                return true;
            }
        }

        return false;
    }
}
