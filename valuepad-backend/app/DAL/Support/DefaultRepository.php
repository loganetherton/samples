<?php
namespace ValuePad\DAL\Support;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Service\RepositoryInterface;

class DefaultRepository extends EntityRepository implements RepositoryInterface
{
	const ALIAS = 't';

	/**
	 * @param array $criteria
	 * @return object[]
	 */
	public function retrieveAll(array $criteria)
	{
		$builder = $this->getEntityManager()->createQueryBuilder();

		$builder
			->select(static::ALIAS)
			->from($this->getEntityName(), static::ALIAS);

		$this->applyCriteria($builder, $criteria);

		return $builder->getQuery()->getResult();
	}

	/**
	 * @param array $criteria
	 * @return object|null
	 */
	public function retrieve(array $criteria)
	{
		$builder = $this->getEntityManager()->createQueryBuilder();

		$builder
			->select(static::ALIAS)
			->from($this->getEntityName(), static::ALIAS);

		$this->applyCriteria($builder, $criteria);

		return $builder->getQuery()->getOneOrNullResult();
	}

	/**
	 * @param array $criteria
	 * @return bool
	 */
	public function exists(array $criteria)
	{
		return (bool) $this->count($criteria);
	}

	/**
	 * @param array $criteria
	 * @return int
	 */
	public function count(array $criteria)
	{
		$builder = $this->getEntityManager()->createQueryBuilder();

		$builder
			->select($builder->expr()->count(static::ALIAS))
			->from($this->getEntityName(), static::ALIAS);

		$this->applyCriteria($builder, $criteria);

		return (int) $builder->getQuery()->getSingleScalarResult();
	}

	/**
	 * @param array $criteria
	 */
	public function delete(array $criteria)
	{
		$builder = $this->getEntityManager()->createQueryBuilder();

		$builder
			->delete()
			->from($this->getEntityName(), static::ALIAS);

		$this->applyCriteria($builder, $criteria);

		$builder->getQuery()->execute();
	}

	/**
	 * @param QueryBuilder $builder
	 * @param array $criteria
	 */
	private function applyCriteria(QueryBuilder $builder, array $criteria)
	{
		foreach ($criteria as $column => $value) {
			if (!is_array($value)) {
				$value = ['=', $value];
			}

			$parts = explode(':', $column);

			$column = $parts[0];
			$placeholder = array_take($parts, 1, $column);

			$operation = $value[0];
			$value = $value[1];

			if (strtolower($operation) == 'in') {
				$builder
					->andWhere($builder->expr()->in(static::ALIAS.'.'.$column, ':'.$placeholder));
			} elseif (strtolower($operation) == 'have member'){
				$builder
					->andWhere($builder->expr()->isMemberOf(':'.$placeholder, static::ALIAS.'.'.$column));
			}else {
				$builder
					->andWhere(static::ALIAS.'.'.$column.' '.$operation.' :'.$placeholder);

			}

			$builder->setParameter($placeholder, $value);
		}
	}
}
