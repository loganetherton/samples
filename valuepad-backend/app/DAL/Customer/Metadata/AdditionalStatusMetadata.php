<?php
namespace ValuePad\DAL\Customer\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class AdditionalStatusMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('additional_statuses');

		$this->defineId($builder);

		$builder
			->createField('title', 'string')
			->build();

		$builder
			->createField('comment', 'text')
			->nullable(true)
			->build();

		$builder
			->createField('isActive', 'boolean')
			->build();

		$builder
			->createManyToOne('customer', Customer::class)
			->build();
	}
}
