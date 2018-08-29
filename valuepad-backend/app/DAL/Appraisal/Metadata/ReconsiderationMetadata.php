<?php
namespace ValuePad\DAL\Appraisal\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\AdditionalDocument;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\DAL\Appraisal\Types\ComparablesType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class ReconsiderationMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('reconsiderations');

		$this->defineId($builder);

		$builder
			->createField('createdAt', 'datetime')
			->build();

		$builder
			->createField('comment', 'text')
			->build();

        $builder
            ->createManyToOne('document', AdditionalDocument::class)
            ->build();

		$builder
			->createField('comparables', ComparablesType::class)
			->build();

		$builder
			->createManyToOne('order', Order::class)
			->build();

		$builder
			->createManyToMany('documents', AdditionalDocument::class)
			->setJoinTable('reconsiderations_documents')
			->addInverseJoinColumn('document_id', 'id')
			->build();
	}
}
