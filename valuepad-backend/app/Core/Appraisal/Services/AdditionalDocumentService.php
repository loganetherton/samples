<?php
namespace ValuePad\Core\Appraisal\Services;

use Ascope\Libraries\Validation\Rules\Email;
use ValuePad\Core\Appraisal\Criteria\AdditionalDocumentSorterResolver;
use ValuePad\Core\Appraisal\Emails\AppraiserAdditionalDocumentEmail;
use ValuePad\Core\Appraisal\Entities\AdditionalDocument;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Options\FetchAdditionalDocumentsOptions;
use ValuePad\Core\Shared\Exceptions\InvalidEmailException;
use ValuePad\Core\Appraisal\Notifications\CreateAdditionalDocumentNotification;
use ValuePad\Core\Appraisal\Notifications\DeleteAdditionalDocumentNotification;
use ValuePad\Core\Appraisal\Persistables\AdditionalDocumentPersistable;
use ValuePad\Core\Appraisal\Validation\AdditionalDocumentValidator;
use ValuePad\Core\Customer\Entities\AdditionalDocumentType;
use ValuePad\Core\Support\Criteria\Sorting\Sorter;
use ValuePad\Core\Support\Letter\EmailerInterface;
use ValuePad\Core\Support\Letter\LetterPreferenceInterface;
use ValuePad\Core\Support\Service\AbstractService;

class AdditionalDocumentService extends AbstractService
{
	use CommonsTrait;

	/**
	 * @param int $orderId
	 * @param AdditionalDocumentPersistable $persistable
	 * @return AdditionalDocument
	 */
	public function create($orderId,  AdditionalDocumentPersistable $persistable)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->getReference(Order::class, $orderId);

		(new AdditionalDocumentValidator($this->container, $order->getCustomer()))->validate($persistable);

		$additionalDocument = $this->createAdditionalDocumentInMemory($order, $persistable, $this->container);

		$this->entityManager->persist($additionalDocument);
		$this->entityManager->flush();

		$this->notify(new CreateAdditionalDocumentNotification($additionalDocument));

		return $additionalDocument;
	}

	/**
	 * @param int $id
	 * @return AdditionalDocument
	 */
	public function get($id)
	{
		return $this->entityManager->find(AdditionalDocument::class, $id);
	}

	/**
	 * @param int $orderId
	 * @param FetchAdditionalDocumentsOptions $options
	 * @return AdditionalDocument[]
	 */
	public function getAll($orderId, FetchAdditionalDocumentsOptions $options = null)
	{
		if ($options === null){
			$options = new FetchAdditionalDocumentsOptions();
		}

		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->select('d')
			->from(AdditionalDocument::class, 'd')
			->where($builder->expr()->eq('d.order', $orderId));

		(new Sorter())->apply($builder, $options->getSortables(), new AdditionalDocumentSorterResolver());

		return $builder->getQuery()->getResult();
	}

	/**
	 * @param int $orderId
	 * @return AdditionalDocumentType[]
	 */
	public function getTypes($orderId)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $orderId);

		return $this->entityManager
			->getRepository(AdditionalDocumentType::class)
			->findBy(['customer' => $order->getCustomer()->getId()]);
	}

	/**
	 * @param int $orderId
	 * @param int $documentId
	 * @return bool
	 */
	public function hasWithDocument($orderId, $documentId)
	{
		return $this->entityManager
			->getRepository(AdditionalDocument::class)
			->exists(['order' => $orderId, 'document' => $documentId]);
	}

	/**
	 * @param int $documentId
	 * @param string $recipient
	 */
	public function emailOnAppraiserBehalf($documentId, $recipient)
	{
		if ($error = (new Email())->check($recipient)){
			throw new InvalidEmailException($error->getMessage());
		}

		/**
		 * @var AdditionalDocument $additionalDocument
		 */
		$additionalDocument = $this->entityManager->find(AdditionalDocument::class, $documentId);;

		/**
		 * @var EmailerInterface $emailer
		 */
		$emailer = $this->container->get(EmailerInterface::class);

		/**
		 * @var LetterPreferenceInterface $preference
		 */
		$preference = $this->container->get(LetterPreferenceInterface::class);

		$email = new AppraiserAdditionalDocumentEmail($additionalDocument);

		$appraiser = $additionalDocument->getOrder()->getAssignee();

		$email->setSender($preference->getNoReply(), $appraiser->getDisplayName());
		$email->addRecipient($recipient);

		$emailer->send($email);
	}

	/**
	 * @param int $id
	 */
	public function delete($id)
	{
		/**
		 * @var AdditionalDocument $document
		 */
		$document = $this->entityManager->find(AdditionalDocument::class, $id);

		$this->notify(new DeleteAdditionalDocumentNotification($document));

		$this->removeFromMemory($document);
		$this->entityManager->flush();
	}

	/**
	 * @param int $orderId
	 */
	public function deleteAll($orderId)
	{
		/**
		 * @var AdditionalDocument[] $documents
		 */
		$documents = $this->entityManager
			->getRepository(AdditionalDocument::class)
			->findBy(['order' => $orderId]);

		foreach ($documents as $document){
			$this->removeFromMemory($document);
		}

		$this->entityManager->flush();
	}



	/**
	 * @param AdditionalDocument $document
	 */
	private function removeFromMemory(AdditionalDocument $document)
	{
		$document->setDocument(null);

		$this->entityManager->remove($document);
	}
}
