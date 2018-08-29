<?php
namespace ValuePad\Core\Invitation\Services;

use ValuePad\Core\Invitation\Criteria\SorterResolver;
use ValuePad\Core\Support\Criteria\Sorting\Sortable;

class ResponderService
{
	/**
	 * @param Sortable $sortable
	 * @return bool
	 */
	public function canResolveSortable(Sortable $sortable)
	{
		return (new SorterResolver())->canResolve($sortable);
	}
}
