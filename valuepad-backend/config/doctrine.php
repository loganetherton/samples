<?php
return [
    'db' => env('DOCTRINE_DB', 'default'),

    'connections' => [
        'default' => [
            'driver' => 'pdo_mysql',
            'user' => env('DOCTRINE_DEFAULT_USER'),
            'password' => env('DOCTRINE_DEFAULT_PASSWORD'),
            'dbname' => env('DOCTRINE_DEFAULT_DBNAME'),
			'charset' => 'utf8',
            'host' => env('DOCTRINE_DEFAULT_HOST')
        ],
        'lite' => [
            'driver' => 'pdo_sqlite',
            'path' => storage_path(env('DOCTRINE_LITE_PATH'))
        ]
    ],

    'cache' => env('APP_DEBUG', false)
        ? Doctrine\Common\Cache\ArrayCache::class
        : Doctrine\Common\Cache\ApcuCache::class,

    'proxy' => [
        'auto' => env('APP_DEBUG', false)
            ? Doctrine\Common\Proxy\AbstractProxyFactory::AUTOGENERATE_ALWAYS
            : Doctrine\Common\Proxy\AbstractProxyFactory::AUTOGENERATE_NEVER,
        'dir' => storage_path('proxies'),
        'namespace' => 'ValuePad\Temp\Proxies'
    ],
    'migrations' => [
        'dir' => database_path('migrations'),
        'namespace' => 'ValuePad\Migrations',
        'table' => 'doctrine_migrations'
    ],
	'entities' => [
        ValuePad\DAL\Location\Support\Place::class =>
            ValuePad\DAL\Location\Support\PlaceMetadata::class,

        ValuePad\Letter\Support\Frequency::class =>
            ValuePad\Letter\Support\FrequencyMetadata::class,

        ValuePad\Support\Chance\Attempt::class =>
            ValuePad\Support\Chance\AttemptMetadata::class,

        ValuePad\Push\Support\Story::class =>
            ValuePad\Push\Support\StoryMetadata::class
	],

    'types' => [
        ValuePad\DAL\Location\Support\ErrorType::class
    ]
];
