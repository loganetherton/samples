<?php
namespace ValuePad\DAL\Appraiser\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraiser\Entities\Eo;
use ValuePad\Core\Appraiser\Entities\EoEx;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class EoMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('eo')
            ->setSingleTableInheritance()
            ->setDiscriminatorColumn('discriminator', 'string', 20)
            ->addDiscriminatorMapClass('normal', Eo::class)
            ->addDiscriminatorMapClass('extended', EoEx::class);

		$this->defineId($builder);

		$builder
			->createManyToOne('document', Document::class)
			->build();

		$builder
			->createField('claimAmount', 'decimal')
			->scale(2)
			->build();

		$builder
			->createField('aggregateAmount', 'decimal')
			->scale(2)
			->build();

		$builder
			->createField('deductible', 'decimal')
			->scale(2)
			->nullable(true)
			->build();

		$builder
			->createField('expiresAt', 'datetime')
			->build();

		$builder
			->createField('carrier', 'string')
			->build();
	}
}
