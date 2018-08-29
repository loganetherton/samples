<?php
namespace ValuePad\Api\Amc\V2_0\Processors;
use ValuePad\Api\Support\Searchable\BaseSearchableProcessor;
use ValuePad\Api\Support\Searchable\SortableTrait;
use ValuePad\Core\Amc\Services\ResponderService;
use ValuePad\Core\Support\Criteria\Constraint;
use ValuePad\Core\Support\Criteria\Sorting\Sortable;

class InvoicesSearchableProcessor extends BaseSearchableProcessor
{
    use SortableTrait;

    protected function configuration()
    {
        return [
            'filter' => [
                'isPaid' => [
                    'constraint' => Constraint::EQUAL,
                    'type' => 'bool'
                ]
            ]
        ];
    }

    /**
     * @param Sortable $sortable
     * @return bool
     */
    protected function isResolvable(Sortable $sortable)
    {
        /**
         * @var ResponderService $responder
         */
        $responder = $this->container->make(ResponderService::class);

        return $responder->canResolveInvoiceSortable($sortable);
    }
}
