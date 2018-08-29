<?php
namespace ValuePad\Core\Appraisal\Services;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Appraisal\Criteria\MessageFilterResolver;
use ValuePad\Core\Appraisal\Criteria\MessageSorterResolver;
use ValuePad\Core\Appraisal\Entities\Message;
use ValuePad\Core\Appraisal\Options\FetchMessagesOptions;
use ValuePad\Core\Appraisal\Persistables\MessagePersistable;
use ValuePad\Core\Appraisal\Validation\MessageValidator;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Services\PermissionService;
use ValuePad\Core\Support\Criteria\Filter;
use ValuePad\Core\Support\Criteria\Paginator;
use ValuePad\Core\User\Entities\User;

class MessageService extends AbstractMessageFactoryService
{
	/**
	 * @param int $readerId
	 */
	public function markAllAsRead($readerId)
	{
		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->from(Message::class, 'm')
			->select('m');

		$this->byParticipant($builder, $readerId);

		$builder->andWhere(':reader NOT MEMBER OF m.readers')->setParameter('reader', $readerId);

		/**
		 * @var Message[] $messages
		 */
		$messages = $builder->getQuery()->getResult();

		/**
		 * @var User $reader
		 */
		$reader = $this->entityManager->getReference(User::class, $readerId);

		foreach ($messages as $message){
			$message->addReader($reader);
		}

		$this->entityManager->flush();
	}

	/**
	 * @param array $messageIds
	 * @param int $readerId
	 */
	public function markAsRead(array $messageIds, $readerId)
	{
		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->from(Message::class, 'm')
			->select('m');

		$this->byParticipant($builder, $readerId);

		/**
		 * @var Message[] $messages
		 */
		$messages = $builder->andWhere($builder->expr()->in('m.id', ':ids'))
			->setParameter('ids', $messageIds)
			->getQuery()
			->getResult();

		/**
		 * @var User $reader
		 */
		$reader = $this->entityManager->getReference(User::class, $readerId);

		foreach ($messages as $message){

			if ($message->hasReader($reader)){
				continue ;
			}

			$message->addReader($reader);
		}

		$this->entityManager->flush();
	}

	/**
	 * @param int $senderId
	 * @param int $orderId
	 * @param MessagePersistable $persistable
	 */
	protected function validate($senderId, $orderId, MessagePersistable $persistable)
	{
		(new MessageValidator())->validate($persistable);
	}

	/**
	 * @param int $senderId
	 * @param int $orderId
	 * @param MessagePersistable $persistable
	 * @return Message
	 */
	protected function instantiate($senderId, $orderId, MessagePersistable $persistable)
	{
		return new Message();
	}

	/**
	 * @param int $id
	 * @return Message
	 */
	public function get($id)
	{
		return $this->entityManager->find(Message::class, $id);
	}

	/**
	 * @param int $orderId
	 * @param FetchMessagesOptions $options
	 * @return Message[]
	 */
	public function getAllByOrderId($orderId, FetchMessagesOptions $options = null)
	{
		if ($options === null){
			$options = new FetchMessagesOptions();
		}

		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->select('m')
			->from(Message::class, 'm')
			->where($builder->expr()->eq('m.order', ':order'))
			->setParameter('order', $orderId);

		(new Filter())->apply($builder, $options->getCriteria(), new MessageFilterResolver())
			->withSorter($builder, $options->getSortables(), new MessageSorterResolver());

		return (new Paginator())->apply($builder, $options->getPagination());
	}

	/**
	 * @param int $orderId
	 * @param array $criteria
	 * @return int
	 */
	public function getTotalByOrderId($orderId, array $criteria = [])
	{
		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->select($builder->expr()->countDistinct('m'))
			->from(Message::class, 'm')
			->where($builder->expr()->eq('m.order', $orderId));

		(new Filter())->apply($builder, $criteria, new MessageFilterResolver());

		return  (int) $builder->getQuery()->getSingleScalarResult();
	}

	/**
	 * @param int $participantId
	 * @param FetchMessagesOptions $options
	 * @return Message[]
	 */
	public function getAllByParticipantId($participantId, FetchMessagesOptions $options = null)
	{
		if ($options === null){
			$options = new FetchMessagesOptions();
		}

		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->select('m')
			->from(Message::class, 'm');

		$this->byParticipant($builder, $participantId);

		(new Filter())->apply($builder, $options->getCriteria(), new MessageFilterResolver())
			->withSorter($builder, $options->getSortables(), new MessageSorterResolver());

		return (new Paginator())->apply($builder, $options->getPagination());
	}

	/**
	 * @param int $participantId
	 * @param array $criteria
	 * @return int
	 */
	public function getTotalByParticipantId($participantId, array $criteria = [])
	{
		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->select($builder->expr()->countDistinct('m'))
			->from(Message::class, 'm');

		$this->byParticipant($builder, $participantId);

		(new Filter())->apply($builder, $criteria, new MessageFilterResolver());

		return  (int) $builder->getQuery()->getSingleScalarResult();
	}

	/**
	 * @param int $participantId
	 * @return int
	 */
	public function getTotalUnreadByParticipantId($participantId)
	{
		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->select($builder->expr()->countDistinct('m'))
			->from(Message::class, 'm');

		$this->byParticipant($builder, $participantId);

		$builder
			->andWhere(':participant NOT MEMBER OF m.readers')
			->setParameter('participant', $participantId);

		return  (int) $builder->getQuery()->getSingleScalarResult();
	}

	/**
	 * @param int $messageId
	 * @param int $participantId
	 * @return bool
	 */
	public function isReadableByParticipantId($messageId, $participantId)
	{
		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->select($builder->expr()->countDistinct('m'))
			->from(Message::class, 'm');

		$this->byParticipant($builder, $participantId);

		$builder->andWhere($builder->expr()->eq('m.id', ':id'))
			->setParameter('id', $messageId);

		return ((int) $builder->getQuery()->getSingleScalarResult()) > 0;
	}

	/**
	 * @param QueryBuilder $builder
	 * @param $participantId
	 * @return QueryBuilder
	 */
	private function byParticipant(QueryBuilder $builder, $participantId)
	{
        /**
         * @var PermissionService $permissionService
         */
        $permissionService = $this->container->get(PermissionService::class);

        $participantIds = array_map(function(Appraiser $appraiser){
            return $appraiser->getId();
        }, $permissionService->getAllAppraisersByManagerId($participantId));

        $participantIds[] = $participantId;

		return $builder
			->leftJoin('m.order', 'o')
			->where('o.assignee IN(:participants) OR o.customer = :participant OR m.sender IN(:participants)')
			->setParameter('participant', $participantId)
            ->setParameter('participants', $participantIds);
	}

    /**
     * @param int $customerId
     * @param int $assigneeId
     * @param FetchMessagesOptions $options
     * @return Message[]
     */
    public function getAllByCustomerAndAssigneeIds($customerId, $assigneeId, FetchMessagesOptions $options = null)
    {
        if ($options === null){
            $options = new FetchMessagesOptions();
        }

        $builder = $this->startQueryByCustomerAndAssigneeIds($customerId, $assigneeId);

        (new Filter())->apply($builder, $options->getCriteria(), new MessageFilterResolver())
            ->withSorter($builder, $options->getSortables(), new MessageSorterResolver());

        return (new Paginator())->apply($builder, $options->getPagination());
    }

    /**
     * @param int $customerId
     * @param int $assigneeId
     * @param array $criteria
     * @return int
     */
    public function getTotalByCustomerAndAssigneeIds($customerId, $assigneeId, array $criteria = [])
    {
        $builder = $this->startQueryByCustomerAndAssigneeIds($customerId, $assigneeId, true);

        (new Filter())->apply($builder, $criteria, new MessageFilterResolver());

        return  (int) $builder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $customerId
     * @param int $assigneeId
     * @return int
     */
    public function getTotalUnreadByCustomerAndAssigneeIds($customerId, $assigneeId)
    {
        $builder = $this->startQueryByCustomerAndAssigneeIds($customerId, $assigneeId, true);

        $builder->andWhere(':assignee NOT MEMBER OF m.readers');

        return  (int) $builder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $customerId
     * @param int $assigneeId
     * @param bool $isCount
     * @return QueryBuilder
     */
    private function startQueryByCustomerAndAssigneeIds($customerId, $assigneeId, $isCount = false)
    {
        $builder = $this->entityManager->createQueryBuilder();

        $builder
            ->select($isCount ? $builder->expr()->countDistinct('m') : 'm')
            ->from(Message::class, 'm')
            ->leftJoin('m.order', 'o')
            ->where('(o.assignee = :assignee OR m.sender = :assignee) AND o.customer = :customer')
            ->setParameter('assignee', $assigneeId)
            ->setParameter('customer', $customerId);

        return $builder;
    }

	/**
	 * @param int $orderId
	 */
	public function deleteAll($orderId)
	{
		$this->entityManager->getRepository(Message::class)->delete(['order' => $orderId]);
	}

	/**
	 * @param array $messageIds
	 * @param int $senderId
	 */
	public function deleteSelectedBySenderId(array $messageIds, $senderId)
	{
		$this->entityManager->getRepository(Message::class)
			->delete(['sender' => $senderId, 'id' => ['in', $messageIds]]);
	}

	/**
	 * @param int $messageId
	 * @param int $senderId
	 */
	public function deleteBySenderId($messageId, $senderId)
	{
		$this->entityManager->getRepository(Message::class)
			->delete(['sender' => $senderId, 'id' => $messageId]);
	}
}
