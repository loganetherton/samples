<?php
namespace ValuePad\Core\Appraisal\Criteria;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\Sorting\AbstractResolver;

class MessageSorterResolver extends AbstractResolver
{
	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byCreatedAt(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('m.createdAt', $direction);
	}
}
