<?php
namespace ValuePad\DAL\Appraisal\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class BidMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$this->defineId($builder);

		$builder->setTable('bids');

		$builder
			->createField('amount', 'float')
			->build();

		$builder
			->createField('estimatedCompletionDate', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createOneToOne('order', Order::class)
			->build();

		$builder
			->createManyToMany('appraisers', Appraiser::class)
			->setJoinTable('bids_appraisers')
			->build();

		$builder
			->createField('comments', 'string')
			->nullable(true)
			->build();
	}
}
