<?php
namespace ValuePad\DAL\Payment\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Payment\Types\AccountTypeType;
use ValuePad\DAL\Shared\Types\SecretType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class ProfileReferenceMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('authorize_net_references');

		$this->defineId($builder);

		$builder->createField('profileId', 'string')
			->build();

		$builder->createField('creditCardProfileId', 'string')
            ->nullable(true)
			->build();

        $builder->createField('bankAccountProfileId', 'string')
            ->nullable(true)
            ->build();

		$builder->createManyToOne('owner', User::class)
            ->addJoinColumn('owner_id', 'id', true, false, 'CASCADE')
			->build();

        $builder
            ->createField('maskedCreditCardNumber', SecretType::class)
            ->nullable(true)
            ->build();

        $builder
            ->createField('maskedAccountNumber', SecretType::class)
            ->nullable(true)
            ->build();

        $builder
            ->createField('maskedRoutingNumber', SecretType::class)
            ->nullable(true)
            ->build();

        $builder
            ->createField('nameOnAccount', 'string')
            ->length(50)
            ->nullable(true)
            ->build();

        $builder
            ->createField('bankName', 'string')
            ->length(100)
            ->nullable(true)
            ->build();

        $builder
            ->createField('accountType', AccountTypeType::class)
            ->nullable(true)
            ->build();

        $builder
            ->createField('address', 'string')
            ->length(self::ADDRESS_LENGTH)
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

    }
}
