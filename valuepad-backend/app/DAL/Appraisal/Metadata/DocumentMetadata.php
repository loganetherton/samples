<?php
namespace ValuePad\DAL\Appraisal\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Document\Entities\Document as Source;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class DocumentMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('order_documents');

		$this->defineId($builder);

		$builder
			->createField('createdAt', 'datetime')
			->build();

		$builder
			->createField('showToAppraiser', 'boolean')
			->nullable(true)
			->build();

		$builder
			->createManyToMany('primaries', Source::class)
			->setJoinTable('order_documents_primaries')
			->addInverseJoinColumn('primary_document_id', 'id')
			->build();

		$builder
			->createManyToMany('extra', Source::class)
			->setJoinTable('order_documents_extra')
			->addInverseJoinColumn('extra_document_id', 'id')
			->build();

		$builder
			->createManyToOne('order', Order::class)
			->build();
	}
}
