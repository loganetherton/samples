<?php
namespace ValuePad\Core\Support\Service;

/**
 *
 *
 */
interface ContainerInterface
{

    /**
     *
     * @param string $abstract
     * @param string|object|array $callerOrParameters
     * @param array $parameters
     * @return object
     */
    public function get($abstract, $callerOrParameters = null, array $parameters = []);

    /**
     *
     * @param callable $method
     * @return mixed
     */
    public function invoke(callable $method);
}
