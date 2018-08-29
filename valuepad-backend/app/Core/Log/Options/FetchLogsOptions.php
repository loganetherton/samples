<?php
namespace ValuePad\Core\Log\Options;

use ValuePad\Core\Shared\Options\CriteriaAwareTrait;
use ValuePad\Core\Shared\Options\PaginationAwareTrait;
use ValuePad\Core\Shared\Options\SortablesAwareTrait;

class FetchLogsOptions
{
	use PaginationAwareTrait;
	use SortablesAwareTrait;
	use CriteriaAwareTrait;
}
