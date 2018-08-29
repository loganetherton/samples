<?php
namespace ValuePad\Core\Amc\Options;
use ValuePad\Core\Shared\Options\CriteriaAwareTrait;
use ValuePad\Core\Shared\Options\PaginationAwareTrait;
use ValuePad\Core\Shared\Options\SortablesAwareTrait;

class FetchInvoicesOptions
{
    use SortablesAwareTrait;
    use CriteriaAwareTrait;
    use PaginationAwareTrait;
}
