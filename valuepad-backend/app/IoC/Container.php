<?php
namespace ValuePad\IoC;

use ValuePad\Core\Support\Service\ContainerInterface;
use Illuminate\Container\Container as IlluminateContainer;

/**
 *
 *
 */
class Container implements ContainerInterface
{
    /**
     * @var IlluminateContainer
     */
    private $container;

    /**
     * @param IlluminateContainer $container
     */
    public function __construct(IlluminateContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $abstract
     * @param string|object|array $callerOrParameters
     * @param array $parameters
     * @return object
     */
    public function get($abstract, $callerOrParameters = null, array $parameters = [])
    {
        if (is_array($callerOrParameters)) {
            $parameters = $callerOrParameters;
        } elseif ($callerOrParameters !== null) {
            $parameters['caller'] = $callerOrParameters;
        }

        return $this->container->make($abstract, $parameters);
    }

    /**
     * @param callable $method
     * @return mixed
     */
    public function invoke(callable $method)
    {
        return $this->container->call($method);
    }
}
