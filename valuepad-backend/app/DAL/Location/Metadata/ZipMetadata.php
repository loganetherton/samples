<?php
namespace ValuePad\DAL\Location\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Location\Entities\County;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class ZipMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('zips');

		$this->defineId($builder);

		$builder->createField('code', 'string')
			->length(self::ZIP_LENGTH)
			->build();

		$builder
			->createManyToOne('county', County::class)
			->inversedBy('zips')
			->build();
	}
}
