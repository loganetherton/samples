<?php
namespace ValuePad\Core\Log\Services;

use Doctrine\DBAL\Query\QueryBuilder;
use ValuePad\Core\Appraisal\Notifications\AwardOrderNotification;
use ValuePad\Core\Appraisal\Notifications\BidRequestNotification;
use ValuePad\Core\Appraisal\Notifications\ChangeAdditionalStatusNotification;
use ValuePad\Core\Appraisal\Notifications\CreateAdditionalDocumentNotification;
use ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification;
use ValuePad\Core\Appraisal\Notifications\CreateOrderNotification;
use ValuePad\Core\Appraisal\Notifications\DeleteAdditionalDocumentNotification;
use ValuePad\Core\Appraisal\Notifications\DeleteDocumentNotification;
use ValuePad\Core\Appraisal\Notifications\DeleteOrderNotification;
use ValuePad\Core\Appraisal\Notifications\ReconsiderationRequestNotification;
use ValuePad\Core\Appraisal\Notifications\RevisionRequestNotification;
use ValuePad\Core\Appraisal\Notifications\UpdateDocumentNotification;
use ValuePad\Core\Appraisal\Notifications\UpdateOrderNotification;
use ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Services\PermissionService;
use ValuePad\Core\Log\Criteria\FilterResolver;
use ValuePad\Core\Log\Criteria\SorterResolver;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Factories\AwardOrderFactory;
use ValuePad\Core\Log\Factories\BidRequestFactory;
use ValuePad\Core\Log\Factories\ChangeAdditionalStatusFactory;
use ValuePad\Core\Log\Factories\CreateAdditionalDocumentFactory;
use ValuePad\Core\Log\Factories\CreateDocumentFactory;
use ValuePad\Core\Log\Factories\CreateOrderFactory;
use ValuePad\Core\Log\Factories\DeleteAdditionalDocumentFactory;
use ValuePad\Core\Log\Factories\DeleteDocumentFactory;
use ValuePad\Core\Log\Factories\DeleteOrderFactory;
use ValuePad\Core\Log\Factories\FactoryInterface;
use ValuePad\Core\Log\Factories\ReconsiderationRequestFactory;
use ValuePad\Core\Log\Factories\RevisionRequestFactory;
use ValuePad\Core\Log\Factories\UpdateDocumentFactory;
use ValuePad\Core\Log\Factories\UpdateOrderFactory;
use ValuePad\Core\Log\Factories\UpdateProcessStatusFactory;
use ValuePad\Core\Log\Notifications\CreateLogNotification;
use ValuePad\Core\Log\Options\FetchLogsOptions;
use ValuePad\Core\Support\Criteria\Criteria;
use ValuePad\Core\Support\Criteria\Filter;
use ValuePad\Core\Support\Criteria\Paginator;
use ValuePad\Core\Support\Service\AbstractService;

class LogService extends AbstractService
{
	/**
	 * @var array
	 */
	private $factories = [
		CreateOrderNotification::class => CreateOrderFactory::class,
		BidRequestNotification::class => BidRequestFactory::class,
		UpdateProcessStatusNotification::class => UpdateProcessStatusFactory::class,
		DeleteOrderNotification::class => DeleteOrderFactory::class,
		UpdateOrderNotification::class => UpdateOrderFactory::class,
		CreateDocumentNotification::class => CreateDocumentFactory::class,
        UpdateDocumentNotification::class => UpdateDocumentFactory::class,
		DeleteDocumentNotification::class => DeleteDocumentFactory::class,
		CreateAdditionalDocumentNotification::class => CreateAdditionalDocumentFactory::class,
		DeleteAdditionalDocumentNotification::class => DeleteAdditionalDocumentFactory::class,
		ChangeAdditionalStatusNotification::class => ChangeAdditionalStatusFactory::class,
		RevisionRequestNotification::class => RevisionRequestFactory::class,
		ReconsiderationRequestNotification::class => ReconsiderationRequestFactory::class,
        AwardOrderNotification::class => AwardOrderFactory::class
	];

    /**
     * @param int $id
     * @return Log
     */
    public function get($id)
    {
        return $this->entityManager->find(Log::class, $id);
    }

	/**
	 * @param object $notification
	 * @return bool
	 */
	public function canCreate($notification)
	{
		$class = get_class($notification);

		if (!isset($this->factories[$class])){
			return false;
		}

		return class_exists($class);
	}

	/**
	 * @param object $notification
	 * @return Log
	 */
	public function create($notification)
	{
		$class = $this->factories[get_class($notification)];

		/**
		 * @var FactoryInterface $factory
		 */
		$factory = $this->container->get($class);

		$log = $factory->create($notification);

		$this->entityManager->persist($log);

		$this->entityManager->flush();

		$this->notify(new CreateLogNotification($log));

		return $log;
	}

	/**
	 * @param int $assigneeId
	 * @param FetchLogsOptions $options
	 * @return Log[]
	 */
	public function getAllByAssigneeId($assigneeId, FetchLogsOptions $options = null)
	{
		return $this->getAllByQuery(['assignee' => $assigneeId], $options);
	}

    /**
     * @param int $customerId
     * @param int $assigneeId
     * @param FetchLogsOptions $options
     * @return Log[]
     */
    public function getAllByCustomerAndAssigneeIds($customerId, $assigneeId, FetchLogsOptions $options = null)
    {
        return $this->getAllByQuery(['assignee' => $assigneeId, 'customer' => $customerId], $options);
    }

	/**
	 * @param int $orderId
	 * @param FetchLogsOptions $options
	 * @return Log[]
	 */
	public function getAllByOrderId($orderId, FetchLogsOptions $options = null)
	{
		return $this->getAllByQuery(['order' => $orderId], $options);
	}

	/**
	 * @param array $parameters
	 * @param FetchLogsOptions|null $options
	 * @return Log[]
	 */
	private function getAllByQuery(array $parameters, FetchLogsOptions $options = null)
	{
		if ($options === null){
			$options = new FetchLogsOptions();
		}

        $builder = $this->startQuery($parameters);

		(new Filter())->apply($builder, $options->getCriteria(), new FilterResolver())
			->withSorter($builder, $options->getSortables(), new SorterResolver());

		return (new Paginator())->apply($builder, $options->getPagination());
	}

	/**
	 * @param int $assigneeId
	 * @param Criteria[] $criteria
	 * @return int
	 */
	public function getTotalByAssigneeId($assigneeId, array $criteria = [])
	{
		return $this->getTotalByQuery(['assignee' => $assigneeId], $criteria);
	}

    /**
     * @param int $customerId
     * @param int $assigneeId
     * @param array $criteria
     * @return int
     */
	public function getTotalByCustomerAndAssigneeId($customerId, $assigneeId, array $criteria = [])
    {
        return $this->getTotalByQuery(['assignee' => $assigneeId, 'customer' => $customerId], $criteria);
    }

	/**
	 * @param int $orderId
	 * @param Criteria[] $criteria
	 * @return int
	 */
	public function getTotalByOrderId($orderId, array $criteria = [])
	{
		return $this->getTotalByQuery(['order' => $orderId], $criteria);
	}

	/**
	 * @param array $parameters
	 * @param Criteria[] $criteria
	 * @return int
	 */
	private function getTotalByQuery(array $parameters, array $criteria)
	{
        $builder = $this->startQuery($parameters, true);

		(new Filter())->apply($builder, $criteria, new FilterResolver());

		return (int) $builder->getQuery()->getSingleScalarResult();
	}

    /**
     * @param array $parameters
     * @param bool $isCount
     * @return QueryBuilder
     */
	private function startQuery(array $parameters, $isCount = false)
    {
        $builder = $this->entityManager->createQueryBuilder();

        $builder
            ->select($isCount ? $builder->expr()->countDistinct('l') : 'l')
            ->from(Log::class, 'l');

        if (isset($parameters['assignee'])) {
            /**
             * @var PermissionService $permissionService
             */
            $permissionService = $this->container->get(PermissionService::class);

            $assigneeIds = array_map(function(Appraiser $appraiser){
                return $appraiser->getId();
            }, $permissionService->getAllAppraisersByManagerId($parameters['assignee']));

            $assigneeIds[] = $parameters['assignee'];

            $parameters['assignee'] = $assigneeIds;
        }

        foreach ($parameters as $name => $value) {
            $op = is_array($value) ? 'in' : 'eq';

            $builder->andWhere($builder->expr()->{$op}('l.'.$name, ':'.$name))
                ->setParameter($name, $value);
        }

        return $builder;
    }

	/**
	 * @param int $orderId
	 */
	public function deleteAllByOrderId($orderId)
	{
		$this->entityManager->getRepository(Log::class)->delete(['order' => $orderId]);
	}
}
