<?php
namespace ValuePad\DAL\Amc\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Amc\Entities\Settings;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Location\Entities\State;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class AmcMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder
            ->createField('companyName', 'string')
            ->build();

        $builder->createField('address1', 'string')
            ->length(self::ADDRESS_LENGTH)
            ->build();

        $builder->createField('address2', 'string')
            ->length(self::ADDRESS_LENGTH)
            ->nullable(true)
            ->build();

        $builder->createField('zip', 'string')
            ->length(self::ZIP_LENGTH)
            ->build();

        $builder->createField('city', 'string')
            ->length(self::CITY_LENGTH)
            ->build();

        $builder->createManyToOne('state', State::class)
            ->addJoinColumn('state', 'code')
            ->build();


        $builder->createField('phone', 'string')
            ->length(self::PHONE_LENGTH)
            ->build();

        $builder->createField('fax', 'string')
            ->length(self::PHONE_LENGTH)
            ->nullable(true)
            ->build();


        $builder->createField('lenders', 'text')
            ->nullable(true)
            ->build();

        $builder
            ->createManyToMany('customers', Customer::class)
            ->mappedBy('amcs')
            ->build();

        /*
		 * A workaround related to this issue https://github.com/doctrine/doctrine2/issues/4389#issuecomment-162367781
		 */
        $builder
            ->createOneToMany('settings', Settings::class)
            ->mappedBy('amc')
            ->cascadeRemove()
            ->build();

        $builder
            ->createField('secret1', 'string')
            ->length(static::SECRET_LENGTH)
            ->build();

        $builder
            ->createField('secret2', 'string')
            ->length(static::SECRET_LENGTH)
            ->build();
    }
}
