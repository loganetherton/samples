<?php
namespace ValuePad\Core\Support\Criteria\Sorting;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\Join;

abstract class AbstractResolver implements ResolverInterface
{
	/**
	 * @param Sortable $sortable
	 * @return bool
	 */
	public function canResolve(Sortable $sortable)
	{
		return method_exists($this, $this->getMethod($sortable->getProperty()));
	}

	/**
	 * @param string $property
	 * @return string
	 */
	private function getMethod($property)
	{
		return 'by' . str_replace('.', '', $property);
	}


	/**
	 * @param QueryBuilder $builder
	 * @param Sortable $sortable
	 * @return null|Join
	 */
	public function resolve(QueryBuilder $builder, Sortable $sortable)
	{
		$method = $this->getMethod($sortable->getProperty());

		return call_user_func([
			$this,
			$method
		], $builder, (string) $sortable->getDirection());
	}
}
