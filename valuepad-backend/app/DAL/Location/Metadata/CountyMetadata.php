<?php
namespace ValuePad\DAL\Location\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Location\Entities\Zip;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class CountyMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('counties');

		$this->defineId($builder);

		$builder->createManyToOne('state', State::class)
			->addJoinColumn('state', 'code')
			->build();

		$builder
			->createField('title', 'string')
			->build();

		$builder
			->createOneToMany('zips', Zip::class)
			->mappedBy('county')
			->build();
	}
}
