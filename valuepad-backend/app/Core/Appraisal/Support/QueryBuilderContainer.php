<?php
namespace ValuePad\Core\Appraisal\Support;
use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\Context;
use ValuePad\Core\Support\Criteria\Join;

class QueryBuilderContainer
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var QueryBuilder
     */
    private $builder;

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->builder = $queryBuilder;
        $this->context = new Context();
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->builder;
    }

    /**
     * @param Join $join
     * @return $this
     */
    public function addJoin(Join $join)
    {
        $this->context->addJoin($join);

        return $this;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }
}
