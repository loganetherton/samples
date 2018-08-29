<?php
namespace ValuePad\DAL\Back\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Back\Types\ValueType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class SettingMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('back_settings');

		$builder
			->createField('name', 'string')
			->makePrimaryKey()
			->build();

		$builder
			->createField('value', ValueType::class)
			->build();
	}
}
