<?php
namespace ValuePad\Core\Support\Criteria\Sorting;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\Mutator;
use RuntimeException;

class Sorter extends Mutator
{
	/**
	 * @param QueryBuilder $builder
	 * @param Sortable[] $sortables
	 * @param ResolverInterface $resolver
	 */
	public function apply(QueryBuilder $builder, array $sortables, ResolverInterface $resolver)
	{
		foreach ($sortables as $sortable){
			$this->resolve($builder, $sortable, $resolver);
		}

		$this->applyJoins($builder);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Sortable $sortable
	 * @param ResolverInterface $resolver
	 */
	private function resolve(QueryBuilder $builder, Sortable $sortable, ResolverInterface $resolver)
	{
		if ($resolver->canResolve($sortable)) {
			$result = $resolver->resolve($builder, $sortable);

			$this->processResult($result);

			return;
		}

		throw new RuntimeException(
			'Unable to resolve sorting by the "'.$sortable->getProperty()
			.'" property with the "'.$sortable->getDirection().'" direction.'
		);
	}

}
