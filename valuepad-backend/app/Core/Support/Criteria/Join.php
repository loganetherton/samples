<?php
namespace ValuePad\Core\Support\Criteria;

/**
 *
 *
 */
class Join
{
    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $alias;

    /**
     * @param string $property
     * @param string $alias
     */
    public function __construct($property, $alias)
    {
        $this->property = $property;
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->property . ' ' . $this->alias;
    }
}
