<?php
namespace ValuePad\Core\Document\Services;

use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Enums\Format;
use ValuePad\Core\Document\Exceptions\InvalidFormatException;
use ValuePad\Core\Document\Interfaces\DocumentPreferenceInterface;
use ValuePad\Core\Document\Options\CreateDocumentOptions;
use ValuePad\Core\Shared\Interfaces\TokenGeneratorInterface;
use ValuePad\Core\Document\Persistables\ExternalDocumentPersistable;
use ValuePad\Core\Document\Support\Storage\StorageInterface;
use ValuePad\Core\Document\Persistables\DocumentPersistable;
use ValuePad\Core\Document\Validation\DocumentValidator;
use ValuePad\Core\Document\Validation\ExternalDocumentValidator;
use ValuePad\Core\Support\Service\AbstractService;
use DateTime;
use Traversable;
use ValuePad\Support\Tracker;

class DocumentService extends AbstractService
{
    /**
     * @param DocumentPersistable $persistable
	 * @param CreateDocumentOptions $options
     * @return Document
     */
    public function create(DocumentPersistable $persistable, CreateDocumentOptions $options = null)
    {
		if ($options === null){
			$options = new CreateDocumentOptions();
		}

		/**
		 * @var StorageInterface $storage
		 */
		$storage = $this->container->get(StorageInterface::class);

		if (!$options->isTrusted()){

			(new DocumentValidator($storage))->validate($persistable);
		}

		$format = Format::toFormat($persistable);

		if (!$options->isTrusted() && $format === null){
			throw new InvalidFormatException();
		}

        $location = $persistable->getLocation();

        $descriptor = $storage->getFileDescriptor($location);

        $document = new Document();

        /**
         * @var TokenGeneratorInterface $tokenGenerator
         */
        $tokenGenerator = $this->container->get(TokenGeneratorInterface::class);

        $document->setToken($tokenGenerator->generate());

        $document->setFormat($format);
        $document->setSize($descriptor->getSize());

        $document->setName($persistable->getSuggestedName());

        $document->setUri('');
        $document->setUploadedAt(new DateTime());

        $this->entityManager->persist($document);
        $this->entityManager->flush();

        $remoteUri = '/documents/' . $document->getId() . '/' . $document->getName();

        $storage->putFileIntoRemoteStorage($location, $remoteUri);

        $document->setUri($remoteUri);

        $this->entityManager->persist($document);
        $this->entityManager->flush();

        return $document;
    }

	/**
	 * @param ExternalDocumentPersistable $persistable
	 * @return Document
	 */
	public function createExternal(ExternalDocumentPersistable $persistable)
	{
		(new ExternalDocumentValidator())->validate($persistable);

		$document = new Document();

		$document->setExternal(true);
		$document->setFormat($persistable->getFormat());
		$document->setSize($persistable->getSize());
		$document->setName($persistable->getName());
		$document->setUri($persistable->getUrl());
		$document->setUploadedAt(new DateTime());

		/**
		 * @var TokenGeneratorInterface $tokenGenerator
		 */
		$tokenGenerator = $this->container->get(TokenGeneratorInterface::class);

		$document->setToken($tokenGenerator->generate());

		$this->entityManager->persist($document);
		$this->entityManager->flush();

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
	 * @param array $ids
	 * @return Document[]
	 */
	public function getAllSelected(array $ids)
	{
		$builder = $this->entityManager->createQueryBuilder();

		return $builder
			->select('d')
			->from(Document::class, 'd')
			->where($builder->expr()->in('d.id', ':ids'))
			->setParameter('ids', $ids)
			->getQuery()
			->getResult();
	}

    /**
     * @param int|int[] $id
     * @return bool
     */
    public function exists($id)
    {
        if (!is_array($id)) {
            return $this->entityManager->getRepository(Document::class)->exists(['id' => $id]);
        }

		$total = $this->entityManager->getRepository(Document::class)->count(['id' => ['in', $id]]);

        return $total == count($id);
    }


	public function deleteAllUnused()
	{
		/**
		 * @var DocumentPreferenceInterface $preference
		 */
		$preference = $this->container->get(DocumentPreferenceInterface::class);

		/**
		 * @var StorageInterface $storage
		 */
		$storage = $this->container->get(StorageInterface::class);

		$builder = $this->entityManager->createQueryBuilder();

		$expression = 'DATE_ADD(d.uploadedAt, '.($preference->getLifetime() * 60).', \'second\')';

		/**
		 * @var Traversable
		 */
		$documents = $builder
			->select('d')
			->from(Document::class, 'd')
			->where($builder->expr()->lt($expression, ':now'))
			->andWhere($builder->expr()->eq('d.usage', ':usage'))
			->setParameters(['now' => new DateTime(), 'usage' => 0])
			->getQuery()
			->iterate();

		$uris = [];

		$tracker = new Tracker($documents, 100);

		foreach($tracker as $document){

			/**
			 * @var Document $document
			 */
			$document = $document[0];

			if (!$document->isExternal()){
				$uris[] = $document->getUri();
			}

			$this->entityManager->remove($document);

			if ($tracker->isTime()){
				$storage->removeFilesFromRemoteStorage($uris);
				$this->entityManager->flush();
				$this->entityManager->clear();
				$uris = [];
			}
		}

		$storage->removeFilesFromRemoteStorage($uris);
		$this->entityManager->flush();
	}
}
