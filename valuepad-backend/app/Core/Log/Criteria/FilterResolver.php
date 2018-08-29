<?php
namespace ValuePad\Core\Log\Criteria;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\AbstractResolver;
use ValuePad\Core\User\Entities\User;

class FilterResolver extends AbstractResolver
{
	/**
	 * @param QueryBuilder $builder
	 * @param User $user
	 */
	public function whereInitiatorNotEqual(QueryBuilder $builder, User $user)
	{
		$builder->andWhere($builder->expr()->neq('l.user', $user->getId()));
	}
}
