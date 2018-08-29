<?php
namespace ValuePad\DAL\Amc\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Amc\Entities\Alias;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\Coverage;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Location\Entities\State;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class LicenseMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('amc_licenses');

        $this->defineId($builder);

        $builder
            ->createField('number', 'string')
            ->length(self::LICENSE_NUMBER_LENGTH)
            ->build();

        $builder
            ->createManyToOne('state', State::class)
            ->addJoinColumn('state', 'code')
            ->build();

        $builder
            ->createField('expiresAt', 'datetime')
            ->nullable(true)
            ->build();


        $builder
            ->createManyToOne('amc', Amc::class)
            ->build();

        $builder
            ->createOneToMany('coverages', Coverage::class)
            ->mappedBy('license')
            ->build();

        $builder
            ->createManyToOne('document', Document::class)
            ->build();

        $builder
            ->createOneToOne('alias', Alias::class)
            ->cascadeRemove()
            ->build();
    }
}
