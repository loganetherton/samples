<?php
namespace ValuePad\IoC;

/**
 *
 *
 */
class Context
{

    /**
     *
     * @var string|object
     */
    private $caller;

    /**
     *
     * @var array
     */
    private $parameters = [];

    /**
     *
     * @var string
     */
    private $abstract;

    /**
     *
     * @param string|object $caller
     */
    public function setCaller($caller)
    {
        $this->caller = $caller;
    }

    /**
     *
     * @return object|string
     */
    public function getCaller()
    {
        return $this->caller;
    }

    /**
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     *
     * @param string $class
     */
    public function setAbstract($class)
    {
        $this->abstract = $class;
    }

    /**
     *
     * @return string
     */
    public function getAbstract()
    {
        return $this->abstract;
    }
}
