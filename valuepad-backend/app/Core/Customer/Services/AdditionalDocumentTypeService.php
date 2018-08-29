<?php
namespace ValuePad\Core\Customer\Services;

use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Appraisal\Entities\AdditionalDocument;
use ValuePad\Core\Customer\Entities\AdditionalDocumentType;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Support\Service\AbstractService;

class AdditionalDocumentTypeService extends AbstractService
{
	/**
	 * @param int $customerId
	 * @param string $title
	 * @return AdditionalDocumentType
	 */
	public function create($customerId, $title)
	{
		$type = new AdditionalDocumentType();

		$type->setTitle($title);

		/**
		 * @var Customer $customer
		 */
		$customer = $this->entityManager->getReference(Customer::class, $customerId);

		$type->setCustomer($customer);

		$this->entityManager->persist($type);
		$this->entityManager->flush();

		return $type;
	}

	/**
	 * @param int $id
	 * @param string $title
	 */
	public function update($id, $title)
	{
		/**
		 * @var AdditionalDocumentType $type
		 */
		$type = $this->entityManager->find(AdditionalDocumentType::class, $id);

		$type->setTitle($title);

		$this->entityManager->flush();
	}

	/**
	 * @param int $customerId
	 * @return AdditionalDocumentType[]
	 */
	public function getAll($customerId)
	{
		return $this->entityManager
			->getRepository(AdditionalDocumentType::class)
			->findBy(['customer' => $customerId]);
	}

	/**
	 * @param int $id
	 */
	public function delete($id)
	{
		$isInUsage = $this->entityManager
			->getRepository(AdditionalDocument::class)
			->exists(['type' => $id]);

		if ($isInUsage){
			throw new PresentableException(
				'The type cannot be deleted since it is already bound to additional documents');
		}

		$type = $this->entityManager->getReference(AdditionalDocumentType::class, $id);

		$this->entityManager->remove($type);

		$this->entityManager->flush();
	}
}
