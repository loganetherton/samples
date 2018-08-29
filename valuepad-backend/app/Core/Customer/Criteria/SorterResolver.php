<?php
namespace ValuePad\Core\Customer\Criteria;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\Sorting\AbstractResolver;

class SorterResolver extends AbstractResolver
{
	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byName(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('c.name', $direction);
	}
}
