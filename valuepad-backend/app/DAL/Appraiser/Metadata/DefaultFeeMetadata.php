<?php
namespace ValuePad\DAL\Appraiser\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\JobType\Entities\JobType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class DefaultFeeMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('default_fees');

		$this->defineId($builder);

		$builder
			->createField('amount', 'float')
			->build();

		$builder
			->createManyToOne('jobType', JobType::class)
			->build();

		$builder
			->createManyToOne('appraiser', Appraiser::class)
			->inversedBy('defaultFees')
			->build();
	}
}
