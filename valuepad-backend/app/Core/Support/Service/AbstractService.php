<?php
namespace ValuePad\Core\Support\Service;

use Doctrine\ORM\EntityManagerInterface;
use Ascope\Libraries\Converter\Transferer\Transferer;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Shared\Interfaces\NotifierInterface;

abstract class AbstractService
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

	/**
	 * @var EnvironmentInterface
	 */
	protected $environment;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->entityManager = $container->get(EntityManagerInterface::class);
		$this->environment = $container->get(EnvironmentInterface::class);

        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

	/**
	 * @param object $notification
	 */
	protected function notify($notification)
	{
		/**
		 * @var NotifierInterface $notifier
		 */
		$notifier = $this->container->get(NotifierInterface::class);

		$notifier->notify($notification);
	}


    /**
     *
     * @param object $src
     * @param object $dest
     * @param array $config
     * @return object
     */
    protected function transfer($src, $dest, array $config = [])
    {
        $transferer = new Transferer($config);
        return $transferer->transfer($src, $dest);
    }
}
