<?php
namespace ValuePad\Support\Chance;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Container\Container;
use DateTime;

class Coordinator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->entityManager = $container->make(EntityManagerInterface::class);
        $this->config = $container->make('config')->get('app.chance');
    }

    public function tryAllAttempts()
    {
        $builder = $this->entityManager->createQueryBuilder();

        $iterator = $builder
            ->from(Attempt::class, 'a')
            ->select('a')
            ->where('(a.createdAt <= :now AND a.attemptedAt IS NULL) OR (a.attemptedAt <= :now AND a.attemptedAt IS NOT NULL)')
            ->setParameter('now', new DateTime('-'.$this->config['waiting_time'].' minutes'))
            ->getQuery()
            ->iterate();

        foreach ($iterator as $item){
            $attempt = $item[0];

            $this->taste($attempt);
        }
    }

    /**
     * @param Attempt $attempt
     */
    private function taste(Attempt $attempt)
    {
        $attempt->increaseQuantity();
        $attempt->setAttemptedAt(new DateTime());

        $this->entityManager->flush();

        $handler = $this->config['handlers'][$attempt->getTag()];

        /**
         * @var LogicHandlerInterface $handler
         */
        $handler = $this->container->make($handler);

        if ($handler->handle($attempt) === true){
            $this->stop($attempt);
        } elseif ($attempt->getQuantity() >= $this->config['max_attempts']){
            $handler->outOfAttempts($attempt);
            $this->stop($attempt);
        }
    }

    /**
     * @param Attempt $attempt
     */
    public function schedule(Attempt $attempt)
    {
        $this->entityManager->persist($attempt);

        $this->entityManager->flush();
    }

    /**
     * @param Attempt $attempt
     */
    public function stop(Attempt $attempt)
    {
        $this->entityManager->remove($attempt);
        $this->entityManager->flush();
    }
}
