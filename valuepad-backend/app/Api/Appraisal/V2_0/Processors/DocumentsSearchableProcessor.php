<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;
use ValuePad\Api\Support\Searchable\BaseSearchableProcessor;
use ValuePad\Api\Support\Searchable\SortableTrait;
use ValuePad\Core\Appraisal\Services\ResponderService;
use ValuePad\Core\Support\Criteria\Sorting\Sortable;

class DocumentsSearchableProcessor extends BaseSearchableProcessor
{
    use SortableTrait;

    /**
     * @param Sortable $sortable
     * @return bool
     */
    protected function isResolvable(Sortable $sortable)
    {
        /**
         * @var ResponderService $responderService
         */
        $responderService = $this->container->make(ResponderService::class);

        return $responderService->canResolverDocumentSortable($sortable);
    }
}
