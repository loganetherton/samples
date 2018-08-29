<?php
namespace ValuePad\Core\Appraisal\Options;

use ValuePad\Core\Shared\Options\CriteriaAwareTrait;
use ValuePad\Core\Shared\Options\PaginationAwareTrait;
use ValuePad\Core\Shared\Options\SortablesAwareTrait;

class FetchMessagesOptions
{
	use PaginationAwareTrait;
	use SortablesAwareTrait;
	use CriteriaAwareTrait;
}
