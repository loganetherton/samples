<?php
namespace ValuePad\Core\Shared\Options;

use ValuePad\Core\Support\Criteria\Sorting\Sortable;

trait SortablesAwareTrait
{
	/**
	 * @var Sortable[]
	 */
	private $sortables = [];

	/**
	 * @param Sortable[] $sortables
	 */
	public function setSortables(array $sortables)
	{
		$this->sortables = $sortables;
	}

	/**
	 * @return Sortable[]
	 */
	public function getSortables()
	{
		return $this->sortables;
	}
}
