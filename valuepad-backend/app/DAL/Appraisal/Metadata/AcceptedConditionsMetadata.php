<?php
namespace ValuePad\DAL\Appraisal\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Appraisal\Types\RequestType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class AcceptedConditionsMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('accepted_conditions');

		$this->defineId($builder);

		$builder
			->createField('request', RequestType::class)
			->build();

		$builder
			->createField('dueDate', 'datetime')
			->nullable(true)
			->build();

		$builder
			->createField('fee', 'float')
			->nullable(true)
			->build();

		$builder
			->createField('explanation', 'text')
			->nullable(true)
			->build();

		$builder
			->createField('additionalComments', 'string')
			->nullable(true)
			->build();
	}
}
