<?php
namespace ValuePad\DAL\Appraisal\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Customer\Entities\AdditionalDocumentType;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class AdditionalDocumentMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('order_additional_documents');

		$this->defineId($builder);

		$builder
			->createManyToOne('type', AdditionalDocumentType::class)
			->build();

		$builder
			->createField('label', 'string')
			->nullable(true)
			->build();

		$builder
			->createManyToOne('document', Document::class)
			->build();

		$builder
			->createManyToOne('order', Order::class)
			->build();

		$builder
			->createField('createdAt', 'datetime')
			->build();
	}
}
