<?php
namespace ValuePad\DAL\Appraisal\Metadata;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class RevisionMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('revisions');

		$this->defineId($builder);

		$builder
			->createField('message', 'text')
			->nullable(true)
			->build();

		$builder
			->createField('checklist', 'json_array')
			->length(MySqlPlatform::LENGTH_LIMIT_TEXT)
			->nullable(true)
			->build();

		$builder
			->createManyToOne('order', Order::class)
			->build();

		$builder
			->createField('createdAt', 'datetime')
			->build();
	}
}
