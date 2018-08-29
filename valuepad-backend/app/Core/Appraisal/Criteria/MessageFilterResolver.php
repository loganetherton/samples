<?php
namespace ValuePad\Core\Appraisal\Criteria;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\AbstractResolver;

class MessageFilterResolver extends AbstractResolver
{
	/**
	 * @param QueryBuilder $builder
	 * @param int $reader
	 */
	public function whereReadersContain(QueryBuilder $builder, $reader)
	{
		$builder
			->andWhere($builder->expr()->isMemberOf(':reader', 'm.readers'))
			->setParameter('reader', $reader);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param int $reader
	 */
	public function whereReadersNotContain(QueryBuilder $builder, $reader)
	{
		$builder
			->andWhere(':reader NOT MEMBER OF m.readers')
			->setParameter('reader', $reader);
	}
}
