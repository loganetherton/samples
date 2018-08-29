<?php
return [
    'implementations' => [

        ValuePad\Core\Support\Service\ContainerInterface::class =>
            ValuePad\IoC\Container::class,

        ValuePad\Core\User\Interfaces\PasswordEncryptorInterface::class =>
            ValuePad\DAL\User\Support\PasswordEncryptor::class,

        ValuePad\Core\Session\Interfaces\SessionPreferenceInterface::class =>
            ValuePad\DAL\Session\Support\SessionPreference::class,

        ValuePad\Core\Document\Support\Storage\StorageInterface::class =>
            ValuePad\DAL\Document\Support\Storage::class,

        ValuePad\Core\Document\Interfaces\DocumentPreferenceInterface::class =>
           ValuePad\DAL\Document\Support\DocumentPreference::class,

        ValuePad\Core\Shared\Interfaces\TokenGeneratorInterface::class =>
            ValuePad\DAL\Shared\Support\TokenGenerator::class,

		ValuePad\Core\Invitation\Interfaces\ReferenceGeneratorInterface::class =>
			ValuePad\DAL\Invitation\Support\ReferenceGenerator::class,

		ValuePad\Core\Shared\Interfaces\NotifierInterface::class =>
			ValuePad\Support\Alert::class,

		ValuePad\Core\Asc\Interfaces\ImporterInterface::class =>
			ValuePad\DAL\Asc\Support\Import\Importer::class,

		ValuePad\Core\Shared\Interfaces\EnvironmentInterface::class =>
			ValuePad\DAL\Support\Environment::class,

		ValuePad\Core\User\Interfaces\ActorProviderInterface::class =>
			ValuePad\DAL\User\Support\ActorProvider::class,

		ValuePad\Core\Support\Letter\EmailerInterface::class =>
			ValuePad\Letter\Support\Emailer::class,

		ValuePad\Core\Support\Letter\LetterPreferenceInterface::class =>
			ValuePad\Letter\Support\LetterPreference::class,

		ValuePad\Core\Payment\Interfaces\PaymentSystemInterface::class =>
			ValuePad\DAL\Payment\Support\PaymentSystem::class,

		ValuePad\Core\User\Interfaces\PasswordPreferenceInterface::class =>
			ValuePad\DAL\User\Support\PasswordPreference::class,
		
		ValuePad\Core\Appraisal\Interfaces\ExtractorInterface::class =>
			ValuePad\DAL\Appraisal\Support\Extractor::class,

		ValuePad\Core\User\Interfaces\DevicePreferenceInterface::class =>
			ValuePad\DAL\User\Support\DevicePreference::class,

		ValuePad\Core\Location\Interfaces\GeocodingInterface::class =>
			ValuePad\DAL\Location\Support\Geocoding::class,
		
		ValuePad\Core\Amc\Interfaces\InvoiceTransformerInterface::class =>
			ValuePad\DAL\Amc\Support\InvoiceTransformer::class,

        ValuePad\Core\Customer\Interfaces\WalletInterface::class =>
            ValuePad\DAL\Customer\Support\Wallet::class,

        ValuePad\Core\Company\Interfaces\DistanceCalculatorInterface::class =>
            ValuePad\DAL\Company\Support\DistanceCalculator::class
    ],

    'factories' => [

    ]
];