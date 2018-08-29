<?php
namespace ValuePad\Core\Invitation\Options;

use ValuePad\Core\Shared\Options\CriteriaAwareTrait;
use ValuePad\Core\Shared\Options\PaginationAwareTrait;
use ValuePad\Core\Shared\Options\SortablesAwareTrait;

class FetchInvitationsOptions
{
	use PaginationAwareTrait;
	use SortablesAwareTrait;
	use CriteriaAwareTrait;
}
