<?php
namespace ValuePad\Core\Appraisal\Services;

use Ascope\Libraries\Validation\Rules\Email;
use ValuePad\Core\Appraisal\Criteria\DocumentSorterResolver;
use ValuePad\Core\Appraisal\Entities\Document;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Emails\AppraiserDocumentEmail;
use ValuePad\Core\Appraisal\Exceptions\ExtractFailedException;
use ValuePad\Core\Appraisal\Interfaces\ExtractorInterface;
use ValuePad\Core\Appraisal\Options\FetchDocumentsOptions;
use ValuePad\Core\Appraisal\Options\FetchMessagesOptions;
use ValuePad\Core\Customer\Enums\Rule;
use ValuePad\Core\Shared\Exceptions\InvalidEmailException;
use ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification;
use ValuePad\Core\Appraisal\Notifications\DeleteDocumentNotification;
use ValuePad\Core\Appraisal\Notifications\UpdateDocumentNotification;
use ValuePad\Core\Appraisal\Objects\DocumentSupportedFormats;
use ValuePad\Core\Appraisal\Options\CreateDocumentOptions;
use ValuePad\Core\Appraisal\Options\UpdateDocumentOptions;
use ValuePad\Core\Appraisal\Persistables\DocumentPersistable;
use ValuePad\Core\Appraisal\Validation\DocumentValidator;
use ValuePad\Core\Customer\Enums\ExtraFormats;
use ValuePad\Core\Customer\Enums\Format;
use ValuePad\Core\Customer\Enums\Formats;
use ValuePad\Core\Support\Criteria\Sorting\Sorter;
use ValuePad\Core\Support\Letter\EmailerInterface;
use ValuePad\Core\Support\Letter\LetterPreferenceInterface;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\Customer\Entities\DocumentSupportedFormats as DocumentSupportedFormatsPerJobType;
use ValuePad\Core\Document\Entities\Document as Source;
use ValuePad\Core\Document\Enums\Format as SourceFormat;
use DateTime;
use ValuePad\Core\Document\Services\DocumentService as SourceService;
use ValuePad\Core\Document\Options\CreateDocumentOptions as CreateSourceOptions;
use Exception;
use Log;

class DocumentService extends AbstractService
{
	/**
	 * @param int $orderId
	 * @param DocumentPersistable $persistable
	 * @param CreateDocumentOptions $options
	 * @return Document
	 */
	public function create($orderId, DocumentPersistable $persistable, CreateDocumentOptions $options = null)
	{
		if ($options === null){
			$options = new CreateDocumentOptions();
		}

		$validator = new DocumentValidator($this->container);

		if ($options->getCheckDocumentSupportedFormats() && !$this->environment->isRelaxed()){
			$validator->setSupportedFormats($this->getSupportedFormats($orderId));
		}

		$validator->validate($persistable);

		$document = new Document();

		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->getReference(Order::class, $orderId);

		$document->setOrder($order);

		if ($createdAt = $this->environment->getLogCreatedAt()){
			$document->setCreatedAt($createdAt);
		} else {
			$document->setCreatedAt(new DateTime());
		}

		$extra = [];

		if ($persistable->getPrimary()){
			/**
			 * @var Source $primary
			 */
			$primary = $this->entityManager->find(Source::class, $persistable->getPrimary()->getId());

			$document->addPrimary($primary);

			if ($primary->getFormat()->is(SourceFormat::ENV)){

				/**
				 * @var ExtractorInterface $extractor
				 */
				$extractor = $this->container->get(ExtractorInterface::class);

				try {
					$primaries = $extractor->fromEnv($primary);
				} catch (Exception $ex){
					Log::error($ex);
					throw new ExtractFailedException('Unable to extract PDF/XML from the provided ENV file.');
				}

				/**
				 * @var SourceService $sourceService
				 */
				$sourceService = $this->container->get(SourceService::class);

				$document->addPrimary($sourceService->create(
					$primaries[SourceFormat::PDF],
					(new CreateSourceOptions())->setTrusted(true))
				);

				if ($xml = array_take($primaries, SourceFormat::XML)){
					$document->addPrimary($sourceService->create($xml, (new CreateSourceOptions())->setTrusted(true)));
				}

				$extra[SourceFormat::ENV] = $primary;
			} elseif ($primary->getFormat()->is(SourceFormat::XML)){
				/**
				 * @var ExtractorInterface $extractor
				 */
				$extractor = $this->container->get(ExtractorInterface::class);

				try {
					$primary = $extractor->fromXml($primary);
				} catch (Exception $ex){
					Log::error($ex);
					throw new ExtractFailedException('Unable to extract PDF from the provided XML file.');
				}

				/**
				 * @var SourceService $sourceService
				 */
				$sourceService = $this->container->get(SourceService::class);

				/** @noinspection PhpParamsInspection */
				$primary = $sourceService->create($primary, (new CreateSourceOptions())->setTrusted(true));

				$document->addPrimary($primary);
			}
		} else {
			foreach ($persistable->getPrimaries() as $primary){

				/**
				 * @var Source $primary
				 */
				$primary = $this->entityManager->getReference(Source::class, $primary->getId());

				$document->addPrimary($primary);
			}
		}

		if ($persistable->getExtra()){

			foreach ($persistable->getExtra()->getIds() as $sourceId){

				/**
				 * @var Source $secondary
				 */
				$secondary = $this->entityManager->find(Source::class, $sourceId);
				$extra[(string) $secondary->getFormat()] = $secondary;
			}
		}

		$document->setExtra(array_values($extra));

		$document->setShowToAppraiser($persistable->getShowToAppraiser());


		$this->entityManager->persist($document);
		$this->entityManager->flush();

		$this->notify(new CreateDocumentNotification($document));

		return $document;
	}

	/**
	 * @param int $id
	 * @return Document
	 */
	public function get($id)
	{
		return $this->entityManager->find(Document::class, $id);
	}

	/**
	 * @param int $orderId
	 * @param FetchDocumentsOptions $options
	 * @return Document[]
	 */
	public function getAll($orderId, FetchDocumentsOptions $options = null)
	{
		if ($options === null){
			$options = new FetchMessagesOptions();
		}

		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->select('d')
			->from(Document::class, 'd')
			->where($builder->expr()->eq('d.order', $orderId));

		(new Sorter())->apply($builder, $options->getSortables(), new DocumentSorterResolver());

		return $builder->getQuery()->getResult();
	}

	/**
	 * @param int $orderId
	 * @return Document
	 */
	public function getRecent($orderId)
	{
		$builder = $this->entityManager->createQueryBuilder();

		return $builder
			->select('d')
			->from(Document::class, 'd')
			->where($builder->expr()->eq('d.order', ':order'))
			->setParameter('order', $orderId)
			->orderBy('d.id', 'desc')
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * @param string $orderId
	 * @return bool
	 */
	public function existsRecent($orderId)
	{
		return $this->entityManager->getRepository(Document::class)->exists(['order' => $orderId]);
	}

	/**
	 * @param int $orderId
	 * @param DocumentPersistable $persistable
	 * @param UpdateDocumentOptions $options
	 */
	public function updateRecent($orderId, DocumentPersistable $persistable, UpdateDocumentOptions $options = null)
	{
		$this->updateDocument($this->getRecent($orderId), $persistable, $options);
	}

	/**
	 * @param int $id
	 * @param DocumentPersistable $persistable
	 * @param UpdateDocumentOptions $options
	 */
	public function update($id, DocumentPersistable $persistable, UpdateDocumentOptions $options = null)
	{
		/**
		 * @var Document $document
		 */
		$document = $this->entityManager->find(Document::class, $id);

		$this->updateDocument($document, $persistable, $options);
	}

	/**
	 * @param Document $document
	 * @param DocumentPersistable $persistable
	 * @param UpdateDocumentOptions $options = null
	 */
	private function updateDocument(
		Document $document,
		DocumentPersistable $persistable,
		UpdateDocumentOptions $options = null
	)
	{
		if ($options === null){
			$options = new UpdateDocumentOptions();
		}

		$validator = new DocumentValidator($this->container);

		if ($options->getCheckDocumentSupportedFormats()){
			$validator->setSupportedFormats($this->getSupportedFormats($document->getOrder()->getId()));
		}

		$validator
			->setExistingExtra($document->getExtra())
			->validate($persistable, true);

		if ($persistable->getExtra()){

			$extra = [];

			foreach ($persistable->getExtra()->getIds() as $extraId){
				$extra[] = $this->entityManager->getReference(Source::class, $extraId);
			}

			$document->setExtra($extra);
		}

		if ($persistable->getShowToAppraiser() !== null){
			$document->setShowToAppraiser($persistable->getShowToAppraiser());
		} elseif (in_array('showToAppraiser', $options->getPropertiesScheduledToClear())){
			$document->setShowToAppraiser(null);
		}

		$this->entityManager->flush();

		$this->notify(new UpdateDocumentNotification($document));
	}

	/**
	 * @param int $orderId
	 * @return DocumentSupportedFormats
	 */
	public function getSupportedFormats($orderId)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $orderId);

		/**
		 * @var DocumentSupportedFormatsPerJobType $formatsPerJobType
		 */
		$formatsPerJobType = $this->entityManager
			->getRepository(DocumentSupportedFormatsPerJobType::class)
			->findOneBy(['jobType' => $order->getJobType()->getId()]);

		$formats = new DocumentSupportedFormats();

		if ($formatsPerJobType){

			if ($formatsPerJobType->getExtra()){
				$formats->setExtra($formatsPerJobType->getExtra());
			}

			$formats->setPrimary($formatsPerJobType->getPrimary());
		} else {
			$formats->setPrimary(new Formats([
				new Format(Format::PDF),
				new Format(Format::XML)
			]));

			$formats->setExtra(new ExtraFormats());
		}

		if (array_take($order->getRules(), Rule::REQUIRE_ENV, false)){
			$formats->setPrimary(new Formats([new Format(Format::ENV)]));
		}

		return $formats;
	}

	/**
	 * @param int $orderId
	 * @param string $recipient
	 */
	public function emailRecentOnAppraiserBehalf($orderId, $recipient)
	{
		if ($error = (new Email())->check($recipient)){
			throw new InvalidEmailException($error->getMessage());
		}

		$document = $this->getRecent($orderId);

		/**
		 * @var EmailerInterface $emailer
		 */
		$emailer = $this->container->get(EmailerInterface::class);

		/**
		 * @var LetterPreferenceInterface $preference
		 */
		$preference = $this->container->get(LetterPreferenceInterface::class);

		$email = new AppraiserDocumentEmail($document);

		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $orderId);

		$appraiser = $order->getAssignee();

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
		 * @var Document $document
		 */
		$document = $this->entityManager->find(Document::class, $id);

		$this->notify(new DeleteDocumentNotification($document));

		$this->removeFromMemory($document);
		$this->entityManager->flush();
	}

	/**
	 * @param int $orderId
	 */
	public function deleteAll($orderId)
	{
		/**
		 * @var Document[] $documents
		 */
		$documents = $this->entityManager->getRepository(Document::class)
			->findBy(['order' => $orderId]);

		foreach ($documents as $document){
			$this->removeFromMemory($document);
		}

		$this->entityManager->flush();
	}

	/**
	 * @param Document $document
	 */
	private function removeFromMemory(Document $document)
	{
		$document->clearExtra();
		$document->clearPrimaries();

		$this->entityManager->remove($document);
	}
}
