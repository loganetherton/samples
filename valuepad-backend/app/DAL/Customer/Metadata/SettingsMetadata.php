<?php
namespace ValuePad\DAL\Customer\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\DAL\Customer\Types\CriticalityType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class SettingsMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('customer_settings');

		$builder
			->createOneToOne('customer', Customer::class)
			->makePrimaryKey()
			->build();

		$builder
			->createField('pushUrl', 'string')
			->nullable(true)
			->build();

		$builder
			->createField('daysPriorInspectionDate', 'integer')
			->build();

		$builder
			->createField('daysPriorEstimatedCompletionDate', 'integer')
			->build();

		$builder
			->createField('preventViolationOfDateRestrictions', CriticalityType::class)
			->build();

		$builder
			->createField('showClientToAppraiser', 'boolean')
			->build();

		$builder
			->createField('showDocumentsToAppraiser', 'boolean')
			->build();

		$builder
			->createField('disallowChangeJobTypeFees', 'boolean')
			->build();

		$builder
			->createField('isSmsEnabled', 'boolean')
			->build();

        $builder
            ->createField('unacceptedReminder', 'integer')
            ->nullable(true)
            ->build();

        $builder
			->createField('removeAccountingData', 'boolean')
			->build();
	}
}
