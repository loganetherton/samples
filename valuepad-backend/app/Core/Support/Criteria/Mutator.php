<?php
namespace ValuePad\Core\Support\Criteria;

use Doctrine\ORM\QueryBuilder;

abstract class Mutator
{
	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * @param Context|null $context
	 */
	public function __construct(Context $context = null)
	{
		if ($context === null){
			$this->context = new Context();
		} else {
			$this->context = $context;
		}
	}

	/**
	 * @param QueryBuilder $builder
	 */
	protected function applyJoins(QueryBuilder $builder)
	{
		foreach ($this->context->getUnusedJoins() as $join) {
			$builder->leftJoin($join->getProperty(), $join->getAlias());
			$this->context->useJoin($join);
		}
	}

	/**
	 * @param null|Join[]|Join $result
	 */
	protected function processResult($result)
	{
		if ($result === null){
			return ;
		}

		if ($result instanceof Join) {
			$result = [$result];
		}

		foreach ($result as $join){
			if (!$this->context->hasJoin($join)){
				$this->context->addJoin($join);
			}
		}
	}
}
