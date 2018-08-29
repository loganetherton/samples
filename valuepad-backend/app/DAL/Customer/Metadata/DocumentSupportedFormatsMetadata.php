<?php
namespace ValuePad\DAL\Customer\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\DAL\Customer\Types\ExtraFormatsType;
use ValuePad\DAL\Customer\Types\FormatsType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class DocumentSupportedFormatsMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$this->defineId($builder);

		$builder->setTable('document_supported_formats');

		$builder
			->createManyToOne('jobType', JobType::class)
			->build();

		$builder
			->createField('primary', FormatsType::class)
			->columnName('`primary`')
			->build();

		$builder
			->createField('extra', ExtraFormatsType::class)
			->nullable(true)
			->build();

		$builder
			->createManyToOne('customer', Customer::class)
			->build();
	}
}
