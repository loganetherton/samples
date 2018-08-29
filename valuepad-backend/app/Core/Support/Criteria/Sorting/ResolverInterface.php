<?php
namespace ValuePad\Core\Support\Criteria\Sorting;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\Join;

interface ResolverInterface
{
	/**
	 * @param Sortable $sortable
	 * @return bool
	 */
	public function canResolve(Sortable $sortable);

	/**
	 * @param QueryBuilder $builder
	 * @param Sortable $sortable
	 * @return null|Join
	 */
	public function resolve(QueryBuilder $builder, Sortable $sortable);
}
