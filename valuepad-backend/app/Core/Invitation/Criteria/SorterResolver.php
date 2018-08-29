<?php
namespace ValuePad\Core\Invitation\Criteria;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\Sorting\AbstractResolver;

class SorterResolver extends AbstractResolver
{
	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byCreatedAt(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('i.createdAt', $direction);
	}
}
