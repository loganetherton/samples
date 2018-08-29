<?php
namespace ValuePad\DAL\Customer\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\JobType\Entities\JobType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class JobTypeMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('customer_job_types');

		$this->defineId($builder);

		$builder
			->createField('title', 'string')
			->build();

		$builder
			->createField('isCommercial', 'boolean')
			->build();

		$builder
			->createField('isPayable', 'boolean')
			->build();

		$builder
			->createManyToOne('local', JobType::class)
			->addJoinColumn('local', 'id')
			->build();

		$builder
			->createField('isHidden', 'boolean')
			->build();

		$builder
			->createManyToOne('customer', Customer::class)
			->build();
	}
}
