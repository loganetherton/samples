<?php
namespace ValuePad\Core\Invitation\Services;

use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\DeclineReason;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Assignee\Services\NotificationSubscriptionService;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Invitation\Criteria\FilterResolver;
use ValuePad\Core\Invitation\Criteria\SorterResolver;
use ValuePad\Core\Invitation\Entities\Invitation;
use ValuePad\Core\Invitation\Enums\Status;
use ValuePad\Core\Invitation\Interfaces\ReferenceGeneratorInterface;
use ValuePad\Core\Invitation\Notifications\AcceptInvitationNotification;
use ValuePad\Core\Invitation\Notifications\DeclineInvitationNotification;
use ValuePad\Core\Invitation\Options\FetchInvitationsOptions;
use ValuePad\Core\Invitation\Persistables\InvitationPersistable;
use ValuePad\Core\Invitation\Validation\InvitationValidator;
use ValuePad\Core\Support\Criteria\Criteria;
use ValuePad\Core\Support\Criteria\Filter;
use ValuePad\Core\Support\Criteria\Paginator;
use ValuePad\Core\Support\Criteria\Sorting\Sorter;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\Asc\Entities\AscAppraiser;
use DateTime;

class InvitationService extends AbstractService
{
	use VerifyRequirementsTrait;

	/**
	 * @param int $customerId
	 * @param InvitationPersistable $persistable
	 * @return Invitation
	 */
	public function create($customerId, InvitationPersistable $persistable)
	{
		/**
		 * @var Customer $customer
		 */
		$customer = $this->entityManager->getReference(Customer::class, $customerId);

		(new InvitationValidator($this->container, $customer))->validate($persistable);

		$invitation = new Invitation();

		if ($appraiser = $persistable->getAppraiser()){
			/**
			 * @var Appraiser $appraiser
			 */
			$appraiser = $this->entityManager->find(Appraiser::class, $appraiser);
			$invitation->setAppraiser($appraiser);
		} else {
			/**
			 * @var AscAppraiser $ascAppraiser
			 */
			$ascAppraiser = $this->entityManager->find(AscAppraiser::class, $persistable->getAscAppraiser());
			$invitation->setAscAppraiser($ascAppraiser);

			if ($appraiser = $ascAppraiser->getAppraiser()){
				$invitation->setAppraiser($appraiser);
			}
		}

		if ($createdAt = $this->environment->getLogCreatedAt()){
			$invitation->setCreatedAt($createdAt);
		} else {
			$invitation->setCreatedAt(new DateTime());
		}

		$invitation->setCustomer($customer);
		$invitation->setStatus(new Status(Status::PENDING));

		if ($persistable->getRequirements() !== null){
			$invitation->setRequirements($persistable->getRequirements());
		}

		/**
		 * @var ReferenceGeneratorInterface $referenceGenerator
		 */
		$referenceGenerator = $this->container->get(ReferenceGeneratorInterface::class);

		$invitation->setReference($referenceGenerator->generate());

		$this->entityManager->persist($invitation);
		$this->entityManager->flush();

		return $invitation;
	}

	/**
	 * @param int $invitationId
	 * @param int $appraiserId
	 */
	public function accept($invitationId, $appraiserId)
	{
		/**
		 * @var Invitation $invitation
		 */
		$invitation = $this->entityManager->find(Invitation::class, $invitationId);

		$customer = $invitation->getCustomer();

		/**
		 * @var AppraiserService $appraiserService
		 */
		$appraiserService = $this->container->get(AppraiserService::class);

		if ($appraiserService->isRelatedWithCustomer($appraiserId, $customer->getId())){
			throw new PresentableException('The appraiser is already connected with the provided customer.');
		}

		if (!$invitation->getStatus()->is(Status::PENDING)){
			throw new PresentableException('The invitation has already been accepted or declined.');
		}

		/**
		 * @var Appraiser $appraiser
		 */
		$appraiser = $this->entityManager->find(Appraiser::class, $appraiserId);

		if (!$this->verifyRequirements($invitation->getRequirements(), $appraiser)){
			throw new PresentableException(
				'The customer requires the following information to be provided in the profile of the appraiser: '
				.implode(', ', $invitation->getRequirements()->toArray()));
		}

		$invitation->setStatus(new Status(Status::ACCEPTED));

		$customer->addAppraiser($appraiser);

		/**
		 * @var Order[] $orders
		 */
		$orders = $this->entityManager->getRepository(Order::class)->findBy(['invitation' => $invitationId]);

		foreach ($orders as $order){
			$order->setInvitation(null);
		}

		$this->entityManager->flush();

        /**
         * @var NotificationSubscriptionService $notificationSubscriptionService
         */
        $notificationSubscriptionService = $this->container->get(NotificationSubscriptionService::class);
        $notificationSubscriptionService->subscribe($appraiserId, $customer->getId());

		$this->notify(new AcceptInvitationNotification($invitation, $appraiser));
	}

	/**
	 * @param int $invitationId
	 */
	public function decline($invitationId)
	{
		/**
		 * @var Invitation $invitation
		 */
		$invitation = $this->entityManager->find(Invitation::class, $invitationId);

		if (!$invitation->getStatus()->is(Status::PENDING)){
			throw new PresentableException('The invitation has already been accepted or declined.');
		}

        /**
         * @var Order[] $orders
         */
        $orders = $this->entityManager->getRepository(Order::class)
            ->retrieveAll([
                'invitation' => $invitationId,
                'processStatus' => ['in', [ProcessStatus::FRESH, ProcessStatus::REQUEST_FOR_BID]]
            ]);


        /**
         * @var OrderService $orderService
         */
        $orderService = $this->container->get(OrderService::class);

        foreach ($orders as $order){
            $orderService->decline($order->getId(), new DeclineReason(DeclineReason::OTHER));
        }

		$invitation->setStatus(new Status(Status::DECLINED));

		$this->entityManager->flush();

		$this->notify(new DeclineInvitationNotification($invitation));
    }

	/**
	 * @param int $id
	 * @return Invitation
	 */
	public function get($id)
	{
		return $this->entityManager->find(Invitation::class, $id);
	}

	/**
	 * @param int $customerId
	 * @param FetchInvitationsOptions $options
	 * @return Invitation[]
	 */
	public function getAllByCustomerId($customerId, FetchInvitationsOptions $options = null)
	{
		if ($options === null){
			$options = new FetchInvitationsOptions();
		}

		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->from(Invitation::class, 'i')
			->select('i')
			->where($builder->expr()->eq('i.customer', ':customer'))
			->setParameter('customer', $customerId);

		(new Filter())->apply($builder, $options->getCriteria(), new FilterResolver())
			->withSorter($builder, $options->getSortables(), new SorterResolver());

		return (new Paginator())->apply($builder, $options->getPagination());
	}

	/**
	 * @param int $customerId
	 * @return int
	 */
	public function getTotalByCustomerId($customerId)
	{
		return $this->entityManager->getRepository(Invitation::class)->count(['customer' => $customerId]);
	}

	/**
	 * @param int $appraiserId
	 * @param FetchInvitationsOptions $options
	 * @return Invitation[]
	 */
	public function getAllByAppraiserId($appraiserId, FetchInvitationsOptions $options = null)
	{
		if ($options === null){
			$options = new FetchInvitationsOptions();
		}

		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->from(Invitation::class, 'i')
			->select('i')
			->where($builder->expr()->eq('i.appraiser', ':appraiser'))
			->setParameter('appraiser', $appraiserId);

		(new Filter())->apply($builder, $options->getCriteria(), new FilterResolver())
			->withSorter($builder, $options->getSortables(), new SorterResolver());

		return (new Paginator())->apply($builder, $options->getPagination());
	}

	/**
	 * @param int $customerId
	 * @param int $appraiserId
	 * @return Invitation
	 */
	public function getSharedAmongCustomerAndAppraiser($customerId, $appraiserId)
	{
		return $this->entityManager->getRepository(Invitation::class)
			->findOneBy(['customer' => $customerId, 'appraiser' => $appraiserId]);
	}

	/**
	 * @param int $appraiserId
	 * @param Criteria[] $criteria
	 * @return int
	 */
	public function getTotalByAppraiserId($appraiserId, array $criteria = [])
	{
		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->from(Invitation::class, 'i')
			->select($builder->expr()->countDistinct('i'))
			->where($builder->expr()->eq('i.appraiser', ':appraiser'))
			->setParameter('appraiser', $appraiserId);

		(new Filter())->apply($builder, $criteria, new FilterResolver());

		return (int) $builder->getQuery()->getSingleScalarResult();

	}
}
