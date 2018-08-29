<?php
namespace ValuePad\Api\Support\Converter\Extractor\Filters;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use ValuePad\Api\Support\Converter\Extractor\FilterInterface;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->session = $container->make(Session::class);
        $this->request = $container->make('request');
        $this->environment = $container->make(EnvironmentInterface::class);
    }

    /**
     * @return bool
     */
    protected function isPost()
    {
        return $this->request->method() === Request::METHOD_POST;
    }

    /**
     * @return string
     */
    protected function getRoute()
    {
        return $this->request->route()->getPath();
    }
}
