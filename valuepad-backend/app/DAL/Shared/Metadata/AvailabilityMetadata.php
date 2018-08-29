<?php
namespace ValuePad\DAL\Shared\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class AvailabilityMetadata extends BaseAvailabilityMetadata
{
	/**
	 *
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('availabilities');
		parent::define($builder);
	}
}
