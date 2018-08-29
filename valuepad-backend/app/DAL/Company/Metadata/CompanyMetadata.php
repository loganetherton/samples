<?php
namespace ValuePad\DAL\Company\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraiser\Entities\Ach;
use ValuePad\Core\Appraiser\Entities\Eo;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Appraiser\Types\CompanyTypeType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class CompanyMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('companies');

        $this->defineId($builder);

        $builder
            ->createField('name', 'string')
            ->build();

        $builder
            ->createField('firstName', 'string')
            ->length(self::FIRST_NAME_LENGTH)
            ->build();

        $builder
            ->createField('lastName', 'string')
            ->length(self::LAST_NAME_LENGTH)
            ->build();

        $builder
            ->createField('email', 'string')
            ->build();

        $builder
            ->createField('phone', 'string')
            ->length(self::PHONE_LENGTH)
            ->build();

        $builder
            ->createField('fax', 'string')
            ->length(self::PHONE_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createField('address1', 'string')
            ->length(self::ADDRESS_LENGTH)
            ->build();

        $builder
            ->createField('address2', 'string')
            ->length(self::ADDRESS_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createField('city', 'string')
            ->length(self::CITY_LENGTH)
            ->build();

        $builder
            ->createManyToOne('state', State::class)
            ->addJoinColumn('state', 'code')
            ->build();

        $builder
            ->createField('zip', 'string')
            ->length(self::ZIP_LENGTH)
            ->build();

        $builder
            ->createField('assignmentZip', 'string')
            ->length(self::ZIP_LENGTH)
            ->build();

        $builder
            ->createField('type', CompanyTypeType::class)
            ->build();

        $builder
            ->createField('otherType', 'string')
            ->nullable(true)
            ->build();

        $builder
            ->createField('taxId', 'string')
            ->columnName('tin')
            ->unique()
            ->length(11)
            ->build();

        $builder
            ->createOneToOne('eo', Eo::class)
            ->cascadeRemove()
            ->build();

        $builder
            ->createOneToOne('w9', Document::class)
            ->build();

        $builder
            ->createOneToOne('ach', Ach::class)
            ->cascadeRemove()
            ->build();

        $builder
            ->createOneToOne('creator', User::class)
            ->build();
    }
}
