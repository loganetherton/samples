<?php
namespace ValuePad\DAL\JobType\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class JobTypeMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('job_types');

		$this->defineId($builder);

		$builder
			->createField('title', 'string')
			->build();
	}
}
