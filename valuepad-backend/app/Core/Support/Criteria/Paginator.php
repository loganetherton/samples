<?php
namespace ValuePad\Core\Support\Criteria;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Shared\Options\PaginationOptions;
use \Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class Paginator
{
	/**
	 * @param QueryBuilder $builder
	 * @param PaginationOptions $pagination
	 * @return DoctrinePaginator
	 */
	public function apply(QueryBuilder $builder, PaginationOptions $pagination)
	{
		$builder
			->setMaxResults($pagination->getPerPage())
			->setFirstResult($pagination->getOffset());

		return new DoctrinePaginator($builder);
	}
}
