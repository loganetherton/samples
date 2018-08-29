<?php
namespace ValuePad\Debug\Support;

use Illuminate\Contracts\Container\Container;
use ValuePad\Core\Shared\Interfaces\NotifierInterface;
use ValuePad\Core\User\Entities\User;
use ValuePad\Mobile\Support\HandlerInterface;

class MobileNotifier implements NotifierInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->storage = new Storage('mobile.json');
    }

    /**
     * @param object $notification
     */
    public function notify($notification)
    {
        $handlers = $this->container->make('config')->get('alert.mobile.handlers', []);

        $class = get_class($notification);

        if (!isset($handlers[$class])){
            return ;
        }

        /**
         * @var HandlerInterface $handler
         */
        $handler = $this->container->make($handlers[$class]);

        $tuple = $handler->handle($notification);

        if ($tuple === null){
            return ;
        }

        if ($this->storage->size() > 100){
            $this->storage->drop();
        }

        $this->storage->store([
            'users' => array_map(function(User $user){ return $user->getId(); }, $tuple->getUsers()),
            'message' => $tuple->getNews()->getMessage(),
            'extra' => $tuple->getNews()->getExtra(),
            'notification' => [
                'category' => $tuple->getNews()->getCategory(),
                'name' => $tuple->getNews()->getName()
            ]
        ]);
    }
}
