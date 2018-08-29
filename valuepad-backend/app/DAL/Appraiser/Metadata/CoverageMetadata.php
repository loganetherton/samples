<?php
namespace ValuePad\DAL\Appraiser\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraiser\Entities\License;
use ValuePad\Core\Location\Entities\County;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class CoverageMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('coverages');

		$this->defineId($builder);

		$builder->createField('zip', 'string')
			->length(self::ZIP_LENGTH)
			->nullable(true)
			->build();

		$builder
			->createManyToOne('county', County::class)
			->build();

		$builder
			->createManyToOne('license', License::class)
			->inversedBy('coverages')
			->build();
	}
}
