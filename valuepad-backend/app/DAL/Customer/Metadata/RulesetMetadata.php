<?php
namespace ValuePad\DAL\Customer\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\Rules;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class RulesetMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('rulesets');

        $this->defineId($builder);

        $builder->createField('level', 'integer')->build();
        $builder->createField('label', 'string')->build();

        $builder
            ->createOneToOne('rules', Rules::class)
            ->cascadeRemove()
            ->build();

        $builder
            ->createManyToOne('customer', Customer::class)
            ->build();
    }
}
