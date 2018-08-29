<?php
namespace ValuePad\DAL\Customer\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class MessageMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder
			->createField('employee', 'string')
			->build();
	}
}
