<?php
namespace ValuePad\Core\Amc\Services;
use ValuePad\Core\Amc\Criteria\InvoiceSorterResolver;
use ValuePad\Core\Support\Criteria\Sorting\Sortable;

class ResponderService
{
    /**
     * @param Sortable $sortable
     * @return bool
     */
    public function canResolveInvoiceSortable(Sortable $sortable)
    {
        return (new InvoiceSorterResolver())->canResolve($sortable);
    }
}
