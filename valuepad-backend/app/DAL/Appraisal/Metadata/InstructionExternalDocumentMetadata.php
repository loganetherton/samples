<?php
namespace ValuePad\DAL\Appraisal\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class InstructionExternalDocumentMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder
			->createManyToOne('order', Order::class)
			->inversedBy('instructionDocument')
			->build();
	}
}
