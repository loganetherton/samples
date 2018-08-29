<?php
namespace ValuePad\DAL\Amc\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Amc\Entities\License;
use ValuePad\Core\Location\Entities\State;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class AliasMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('amc_aliases');

        $this->defineId($builder);

        $builder
            ->createField('companyName', 'string')
            ->nullable(true)
            ->build();

        $builder
            ->createField('address1', 'string')
            ->length(self::ADDRESS_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createField('address2', 'string')
            ->length(self::ADDRESS_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createField('city', 'string')
            ->length(self::CITY_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createManyToOne('state', State::class)
            ->addJoinColumn('state', 'code')
            ->build();

        $builder
            ->createField('zip', 'string')
            ->length(self::ZIP_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createField('phone', 'string')
            ->length(self::PHONE_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createField('fax', 'string')
            ->length(self::PHONE_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createField('email', 'string')
            ->length(self::EMAIL_LENGTH)
            ->nullable(true)
            ->build();
    }
}