<?php
namespace ValuePad\Core\Support\Criteria;

use Doctrine\ORM\QueryBuilder;

abstract class AbstractResolver implements ResolverInterface
{
    /**
     * @param Criteria $criteria
     * @return bool
     */
    public function canResolve(Criteria $criteria)
    {
        return method_exists($this, $this->getMethod($criteria));
    }

    /**
     * @param Criteria $criteria
     * @return string
     */
    private function getMethod(Criteria $criteria)
    {
		$constraint = str_replace('-', '', $criteria->getConstraint());

		if ($criteria->getConstraint()->isNot()){
			$constraint = 'Not'.$constraint;
		}

        return 'where' . str_replace('.', '', $criteria->getProperty()) . $constraint;
    }

    /**
     * @param QueryBuilder $builder
     * @param Criteria $criteria
     * @return Join|null
     */
    public function resolve(QueryBuilder $builder, Criteria $criteria)
    {
        $method = $this->getMethod($criteria);
        return call_user_func([
            $this,
            $method
        ], $builder, $criteria->getValue());
    }
}
