<?php
namespace ValuePad\Core\Customer\Services;

use DateTime;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\Customer\Criteria\SorterResolver;
use ValuePad\Core\Customer\Entities\AdditionalDocumentType;
use ValuePad\Core\Customer\Entities\AdditionalStatus;
use ValuePad\Core\Customer\Entities\Client;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\DocumentSupportedFormats;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Customer\Entities\Ruleset;
use ValuePad\Core\Customer\Entities\Settings;
use ValuePad\Core\Customer\Options\FetchCustomerOptions;
use ValuePad\Core\Customer\Persistables\CustomerPersistable;
use ValuePad\Core\Customer\Validation\CustomerValidator;
use ValuePad\Core\Invitation\Entities\Invitation;
use ValuePad\Core\Invitation\Enums\Status;
use ValuePad\Core\Shared\Interfaces\TokenGeneratorInterface;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Criteria\Sorting\Sorter;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\User\Interfaces\PasswordEncryptorInterface;
use ValuePad\Core\User\Services\UserService;

class CustomerService extends AbstractService
{
	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @param UserService $userService
	 */
	public function initialize(UserService $userService)
	{
		$this->userService = $userService;
	}

	/**
	 * @param $id
	 * @return Customer
	 */
	public function get($id)
	{
		return $this->entityManager->find(Customer::class, $id);
	}

	/**
	 * @return Customer[]
	 */
	public function getAll()
	{
		return $this->entityManager->getRepository(Customer::class)->findAll();
	}

	/**
	 * @param int $appraiserId
	 * @param FetchCustomerOptions $options
	 * @return Customer[]
	 */
	public function getAllByAppraiserId($appraiserId, FetchCustomerOptions $options = null)
	{
		return $this->getAllByUserId($appraiserId, Appraiser::class, $options);
	}

	/**
	 * @param int $amcId
	 * @param FetchCustomerOptions $options
	 * @return Customer[]
	 */
	public function getAllByAmcId($amcId, FetchCustomerOptions $options = null)
	{
		return $this->getAllByUserId($amcId, Amc::class, $options);
	}

	/**
	 * Get all customers that are associated with a particular manager
	 *
	 * @param int $managerId
	 * @param FetchCustomerOptions $options
	 * @return Customer[]
	 */
	public function getAllByManagerId($managerId, FetchCustomerOptions $options = null)
	{
		return $this->getAllByUserId($managerId, Manager::class, $options);
	}

	/**
	 * Get all customers that are associated with a particular user
	 *
	 * @param int $userId
	 * @param string $userType
	 * @param FetchCustomerOptions $options
	 */
	private function getAllByUserId($userId, $userType, FetchCustomerOptions $options = null)
	{
		if ($options === null) {
			$options = new FetchCustomerOptions();
		}

		$relation = [
			Appraiser::class => 'appraisers',
			Amc::class => 'amcs',
			Manager::class => 'managers',
		];

		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->select('c')
			->from(Customer::class, 'c')
			->where($builder->expr()->isMemberOf(':user', 'c.'.$relation[$userType]))
			->setParameter('user', $userId);

		(new Sorter())->apply($builder, $options->getSortables(), new SorterResolver());

		return $builder->getQuery()->getResult();
	}

	/**
	 * Get all customers who have accounting disabled
	 *
	 * @param int $appraiserId
	 * @return Customer[]
	 */
	public function getAllWithDisabledAccounting($appraiserId = null)
	{
		$builder = $this->entityManager->createQueryBuilder();

		$query = $builder->select('c')
			->from(Customer::class, 'c')
			->join('c.settings', 's')
			->andWhere('s.removeAccountingData = :removeAccountingData')
			->setParameter('removeAccountingData', true);

		if ($appraiserId) {
			$query->andWhere($builder->expr()->isMemberOf(':appraiser', 'c.appraisers'))
				->setParameter('appraiser', $appraiserId);
		}

		return $query->getQuery()
			->getResult();
	}

	/**
	 * @param CustomerPersistable $persistable
	 * @return Customer[]
	 */
	public function create(CustomerPersistable $persistable)
	{
		(new CustomerValidator($this->container))->validate($persistable);

		$customer = new Customer();

		$this->transfer($persistable, $customer, [
			'ignore' => ['password']
		]);

		/**
		 * @var PasswordEncryptorInterface $encryptor
		 */
		$encryptor = $this->container->get(PasswordEncryptorInterface::class);
		$customer->setPassword($encryptor->encrypt($persistable->getPassword()));

		$this->entityManager->persist($customer);
		$this->entityManager->flush();

		$settings = new Settings();

		$settings->setCustomer($customer);

		/**
		 * @var TokenGeneratorInterface $generator
		 */
		$generator = $this->container->get(TokenGeneratorInterface::class);

		$customer->setSecret1($generator->generate());
		$customer->setSecret2($generator->generate());

		$this->entityManager->persist($settings);
		$this->entityManager->flush();

		$customer->setSettings($settings);

		return $customer;
	}

	/**
	 * @param int $customerId
	 * @param CustomerPersistable $persistable
	 * @param UpdateOptions $options
	 */
	public function update($customerId, CustomerPersistable $persistable, UpdateOptions $options = null)
	{
		if ($options === null){
			$options = new UpdateOptions();
		}

		/**
		 * @var Customer $customer
		 */
		$customer = $this->entityManager->find(Customer::class, $customerId);

		(new CustomerValidator($this->container))
			->setCurrentCustomer($customer)
			->setForcedProperties($options->getPropertiesScheduledToClear())
			->validate($persistable, true);

		$this->transfer($persistable, $customer, [
			'nullable' => $options->getPropertiesScheduledToClear()
		]);

		$customer->setUpdatedAt(new DateTime());

		$this->entityManager->flush();
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function exists($id)
	{
		return $this->entityManager->getRepository(Customer::class)->exists(['id' => $id]);
	}

	/**
	 * @param int $customerId
	 * @param int $invitationId
	 * @return bool
	 */
	public function hasInvitation($customerId, $invitationId)
	{
		return $this->entityManager
			->getRepository(Invitation::class)
			->exists(['customer' => $customerId, 'id' => $invitationId]);
	}

	/**
	 * @param int $customerId
	 * @param int $ascAppraiserId
	 * @return bool
	 */
	public function hasPendingInvitationForAscAppraiser($customerId, $ascAppraiserId)
	{
		return $this->entityManager
			->getRepository(Invitation::class)
			->exists(['ascAppraiser' => $ascAppraiserId, 'customer' => $customerId, 'status' => Status::PENDING]);
	}

	/**
	 * @param int $customerId
	 * @param int $appraiserId
	 * @return bool
	 */
	public function hasPendingInvitationForAppraiser($customerId, $appraiserId)
	{
		return $this->entityManager
			->getRepository(Invitation::class)
			->exists(['appraiser' => $appraiserId, 'customer' => $customerId]);
	}

	/**
	 * @param int $customerId
	 * @param int $orderId
	 * @return bool
	 */
	public function hasOrder($customerId, $orderId)
	{
		return $this->entityManager
			->getRepository(Order::class)
			->exists(['customer' => $customerId, 'id' => $orderId]);
	}

	/**
	 * @param int $customerId
	 * @return Appraiser[]
	 */
	public function getAllAppraisers($customerId)
	{
		/**
		 * @var Customer $customer
		 */
		$customer = $this->entityManager->find(Customer::class, $customerId);
		return $customer->getAppraisers();
	}

	/**
	 * @param int $customerId
	 * @param int $appraiserId
	 * @return bool
	 */
	public function isRelatedWithAppraiser($customerId, $appraiserId)
	{
		return $this->entityManager
			->getRepository(Customer::class)
			->exists(['appraisers' => ['HAVE MEMBER', $appraiserId], 'id' => $customerId]);
	}

	/**
	 * @param int $customerId
	 * @param int $amcId
	 * @return bool
	 */
	public function isRelatedWithAmc($customerId, $amcId)
	{
		return $this->entityManager
			->getRepository(Customer::class)
			->exists(['amcs' => ['HAVE MEMBER', $amcId], 'id' => $customerId]);
	}

	/**
	 * @param int $customerId
	 * @param int $managerId
	 * @return bool
	 */
	public function isRelatedWithManager($customerId, $managerId)
	{
		return $this->entityManager
			->getRepository(Customer::class)
			->exists(['managers' => ['HAVE MEMBER', $managerId], 'id' => $customerId]);
	}

	/**
	 * @param int $customerId
	 * @param int $formatsId
	 * @return bool
	 */
	public function hasDocumentSupportedFormats($customerId, $formatsId)
	{
		return $this->entityManager
			->getRepository(DocumentSupportedFormats::class)
			->exists(['id' => $formatsId, 'customer' => $customerId]);
	}

	/**
	 * @param int $customerId
	 * @param int $typeId
	 * @return bool
	 */
	public function hasAdditionalDocumentType($customerId, $typeId)
	{
		return $this->entityManager
			->getRepository(AdditionalDocumentType::class)
			->exists(['id' => $typeId, 'customer' => $customerId]);
	}

	/**
	 * @param int $customerId
	 * @param int $jobTypeId
	 * @return bool
	 */
	public function hasVisibleJobType($customerId, $jobTypeId)
	{
		return $this->entityManager
			->getRepository(JobType::class)
			->exists(['customer' => $customerId, 'id' => $jobTypeId, 'isHidden' => false]);
	}

	/**
	 * @param int $customerId
	 * @param int $jobTypeId
	 * @return bool
	 */
	public function hasJobType($customerId, $jobTypeId)
	{
		return $this->entityManager
			->getRepository(JobType::class)
			->exists(['customer' => $customerId, 'id' => $jobTypeId]);
	}

	/**
	 * @param int $customerId
	 * @param int $jobTypeId
	 * @return bool
	 */
	public function hasPayableJobType($customerId, $jobTypeId)
	{
		return $this->entityManager
			->getRepository(JobType::class)
			->exists(['customer' => $customerId, 'id' => $jobTypeId, 'isHidden' => false, 'isPayable' => true]);
	}

	/**
	 * @param int $customerId
	 * @param int $localId
	 * @return bool
	 */
	public function hasPayableJobTypeByLocal($customerId, $localId)
	{
		return $this->entityManager
			->getRepository(JobType::class)
			->exists(['customer' => $customerId, 'local' => $localId, 'isHidden' => false, 'isPayable' => true]);
	}


	/**
	 * @param int $customerId
	 * @param array $jobTypeIds
	 * @return bool
	 */
	public function hasVisibleJobTypes($customerId, array $jobTypeIds)
	{
		return count($jobTypeIds) === $this->entityManager
			->getRepository(JobType::class)
			->count(['customer' => $customerId, 'id' => ['in', $jobTypeIds], 'isHidden' => false]);
	}

	/**
	 * @param int $customerId
	 * @param array $jobTypeIds
	 * @return bool
	 */
	public function hasJobTypes($customerId, array $jobTypeIds)
	{
		return count($jobTypeIds) === $this->entityManager
			->getRepository(JobType::class)
			->count(['customer' => $customerId, 'id' => ['in', $jobTypeIds]]);
	}

	/**
	 * @param int $customerId
	 * @param array $jobTypeIds
	 * @return bool
	 */
	public function hasPayableJobTypes($customerId, array $jobTypeIds)
	{
		return count($jobTypeIds) === $this->entityManager
			->getRepository(JobType::class)
			->count(['customer' => $customerId, 'id' => ['in', $jobTypeIds], 'isHidden' => false, 'isPayable' => true]);
	}

	/**
	 * @param int $customerId
	 * @param int $additionalStatusId
	 * @return bool
	 */
	public function hasActiveAdditionalStatus($customerId, $additionalStatusId)
	{
		return $this->entityManager->getRepository(AdditionalStatus::class)
			->exists(['customer' => $customerId,  'id' => $additionalStatusId, 'isActive' => true]);
	}

	/**
	 * @param int $customerId
	 * @param string $title
	 * @param int $exclude
	 * @return bool
	 */
	public function hasActiveAdditionalStatusByTitle($customerId, $title, $exclude = null)
	{
		$criteria = ['customer' => $customerId, 'title' => $title, 'isActive' => true];

		if ($exclude !== null){
			$criteria['id'] = ['!=', $exclude];
		}

		return $this->entityManager
			->getRepository(AdditionalStatus::class)
			->exists($criteria);
	}

	/**
	 * @param int $customerId
	 * @param int $clientId
	 * @return bool
	 */
	public function hasClient($customerId, $clientId)
	{
		return $this->entityManager->getRepository(Client::class)
			->exists(['customer' => $customerId, 'id' => $clientId]);
	}

	/**
	 * @param int $customerId
	 * @param int $rulesetId
	 * @return bool
	 */
	public function hasRuleset($customerId, $rulesetId)
	{
		return $this->entityManager->getRepository(Ruleset::class)
			->exists(['customer' => $customerId, 'id' => $rulesetId]);
	}

	/**
	 * @param int $customerId
	 * @param array $rulesetIds
	 * @return bool
	 */
	public function hasRulesets($customerId, array $rulesetIds)
	{
		return count($rulesetIds) === $this->entityManager
			->getRepository(Ruleset::class)
			->count(['customer' => $customerId, 'id' => ['in', $rulesetIds]]);
	}

	/**
	 * @param int $customerId
	 * @param int $amcId
	 */
	public function relateWithAmc($customerId, $amcId)
	{
		/**
		 * @var Customer $customer
		 */
		$customer = $this->entityManager->find(Customer::class, $customerId);

		/**
		 * @var Amc $amc
		 */
		$amc = $this->entityManager->find(Amc::class, $amcId);

		$customer->addAmc($amc);

		$this->entityManager->flush();
	}

	/**
	 * Associate a customer with a manager
	 *
	 * @param int $customerId
	 * @param int $managerId
	 */
	public function relateWithManager($customerId, $managerId)
	{
		$customer = $this->entityManager->find(Customer::class, $customerId);

		$manager = $this->entityManager->find(Manager::class, $managerId);

		$customer->addManager($manager);

		$this->entityManager->flush();
	}
}
