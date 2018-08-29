<?php
namespace ValuePad\Core\Customer\Services;

use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\DocumentSupportedFormats;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Customer\Persistables\DocumentSupportedFormatsPersistable;
use ValuePad\Core\Customer\Validation\DocumentSupportedFormatsValidator;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Service\AbstractService;

class DocumentSupportedFormatsService extends AbstractService
{
	/**
	 * @param int $customerId
	 * @param DocumentSupportedFormatsPersistable $persistable
	 * @return DocumentSupportedFormats
	 */
	public function create($customerId, DocumentSupportedFormatsPersistable $persistable)
	{
		/**
		 * @var CustomerService $customerService
		 */
		$customerService = $this->container->get(CustomerService::class);

		/**
		 * @var Customer $customer
		 */
		$customer = $this->entityManager->find(Customer::class, $customerId);

		(new DocumentSupportedFormatsValidator($customerService, $this, $customer))->validate($persistable);

		$formats = new DocumentSupportedFormats();

		$this->exchange($persistable, $formats);

		$formats->setCustomer($customer);

		$this->entityManager->persist($formats);

		$this->entityManager->flush();

		return $formats;
	}

	/**
	 * @param int $id
	 * @param DocumentSupportedFormatsPersistable $persistable
	 * @param UpdateOptions $options
	 */
	public function update(
		$id,
		DocumentSupportedFormatsPersistable $persistable,
		UpdateOptions $options = null
	)
	{
		if ($options === null){
			$options = new UpdateOptions();
		}

		/**
		 * @var DocumentSupportedFormats $formats
		 */
		$formats = $this->entityManager->find(DocumentSupportedFormats::class, $id);

		/**
		 * @var CustomerService $customerService
		 */
		$customerService = $this->container->get(CustomerService::class);

		(new DocumentSupportedFormatsValidator($customerService, $this, $formats->getCustomer()))
			->setForcedProperties($options->getPropertiesScheduledToClear())
			->ignoreJobType($formats->getJobType())
			->validate($persistable, true);


		$this->exchange($persistable, $formats, $options->getPropertiesScheduledToClear());

		$this->entityManager->flush();
	}

	private function exchange(
		DocumentSupportedFormatsPersistable $persistable,
		DocumentSupportedFormats $formats,
		array $forcedProperties = []
	)
	{
		if ($persistable->getJobType() !== null){
			/**
			 * @var JobType $jobType
			 */
			$jobType = $this->entityManager->find(JobType::class, $persistable->getJobType());

			$formats->setJobType($jobType);
		}

		if ($persistable->getPrimary() !== null){
			$formats->setPrimary($persistable->getPrimary());
		}

		if ($persistable->getExtra() !== null){
			$formats->setExtra($persistable->getExtra());
		}

		if (in_array('extra', $forcedProperties)){
			$formats->setExtra(null);
		}
	}

	/**
	 * @param int $customerId
	 * @return DocumentSupportedFormats[]
	 */
	public function getAll($customerId)
	{
		return $this->entityManager
			->getRepository(DocumentSupportedFormats::class)
			->findBy(['customer' => $customerId]);
	}

	/**
	 * @param int $id
	 */
	public function delete($id)
	{
		$formats = $this->entityManager->getReference(DocumentSupportedFormats::class, $id);
		$this->entityManager->remove($formats);
		$this->entityManager->flush();
	}

	/**
	 * @param int $customer
	 * @param int $jobTypeId
	 * @param int $ignored
	 * @return bool
	 */
	public function hasWithJobType($customer, $jobTypeId, $ignored = null)
	{
		$criteria = ['customer' => $customer,  'jobType' => $jobTypeId];

		if ($ignored !== null){
			$criteria['jobType:ignored'] = ['!=', $ignored];
		}

		return $this->entityManager
			->getRepository(DocumentSupportedFormats::class)
			->exists($criteria);
	}
}
