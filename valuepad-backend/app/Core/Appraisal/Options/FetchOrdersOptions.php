<?php
namespace ValuePad\Core\Appraisal\Options;

use ValuePad\Core\Shared\Options\CompanyOrdersAwareTrait;
use ValuePad\Core\Shared\Options\CriteriaAwareTrait;
use ValuePad\Core\Shared\Options\PaginationAwareTrait;
use ValuePad\Core\Shared\Options\SortablesAwareTrait;
use ValuePad\Core\Shared\Options\SubordinatesAwareTrait;

class FetchOrdersOptions
{
	use PaginationAwareTrait;
	use CriteriaAwareTrait;
	use SortablesAwareTrait;
    use SubordinatesAwareTrait;
    use CompanyOrdersAwareTrait;
}
