<?php
namespace ValuePad\Core\Invitation\Criteria;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Invitation\Enums\Status;
use ValuePad\Core\Support\Criteria\AbstractResolver;
use ValuePad\Core\Support\Criteria\Join;

class FilterResolver extends AbstractResolver
{
	/**
	 * @param QueryBuilder $builder
	 * @param Status[] $statuses
	 */
	public function whereStatusIn(QueryBuilder $builder, array $statuses)
	{
		$statuses = array_map(function(Status $status){ return $status->value(); }, $statuses);

		$builder->andWhere($builder->expr()->in('i.status', ':statuses'))->setParameter('statuses', $statuses);
	}

	/**
     * @param QueryBuilder $builder
     * @param string $value
     */
	public function whereLicenseNumberSimilar(QueryBuilder $builder, $value)
    {
		$builder->andWhere($builder->expr()->like('l.number', ':licenseNumber'))
			->setParameter('licenseNumber', '%' . addcslashes($value, '%_') . '%');

		return [new Join('i.appraiser', 'a'), new Join('a.licenses', 'l')];
    }

	/**
	 * @param QueryBuilder $builder
	 * @param string $value
	 */
	public function whereLicenseStateEqual(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->eq('l.state', ':licenseState'))
			->setParameter('licenseState', $value);

		return [new Join('i.appraiser', 'a'), new Join('a.licenses', 'l')];
	}
}
