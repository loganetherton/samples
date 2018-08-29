<?php
namespace ValuePad\Push\Support;
use Illuminate\Container\Container;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Shared\Interfaces\NotifierInterface;

abstract class AbstractNotifier implements NotifierInterface
{
    /**
     * @var Container
     */
    protected $container;

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
        $this->environment = $container->make(EnvironmentInterface::class);
    }

    /**
     * @param object $notification
     * @param array $handlers
     * @param callable $onError
     */
    protected function forward($notification, array $handlers, callable $onError = null)
    {
        $class = get_class($notification);

        if (!isset($handlers[$class])){
            return ;
        }

        /**
         * @var HandlerInterface $handler
         */
        $handler = $this->container->make($handlers[$class]);

        $payload = $handler->handle($notification);

        if (!$payload){
            return ;
        }

        /**
         * @var Processor $processor
         */
        $processor = $this->container->make(Processor::class);

        $processor->process($payload, $onError);
    }
}
