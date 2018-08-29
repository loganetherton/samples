<?php
namespace ValuePad\DAL\Customer\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class AdditionalDocumentTypeMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('additional_document_types');

		$this->defineId($builder);

		$builder
			->createField('title', 'string')
			->build();

		$builder
			->createManyToOne('customer', Customer::class)
			->build();
	}
}
