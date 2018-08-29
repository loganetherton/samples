<?php

$config = [

	'context' => env('APP_CONTEXT', 'development'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG'),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY', 'r4ksy1v1axnz58pbzg3reor2o5vgzyfg'),

    'cipher' => MCRYPT_RIJNDAEL_128,

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => 'single',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        ValuePad\Console\Artisan\ServiceProvider::class,
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Routing\ControllerServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
		ValuePad\Letter\Support\PigeonServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,

		ValuePad\IoC\ServiceProvider::class,
		ValuePad\Support\ProjectServiceProvider::class,
        ValuePad\Api\Support\AppServiceProvider::class,
		ValuePad\Live\Support\AppServiceProvider::class,
        ValuePad\Api\Support\RouteServiceProvider::class,
		ValuePad\Live\Support\RouteServiceProvider::class,
        Barryvdh\Cors\ServiceProvider::class,
        Ascope\Libraries\Kangaroo\ServiceProvider::class,
        Ascope\Libraries\Permissions\ServiceProvider::class,
        Ascope\Libraries\Verifier\ServiceProvider::class,
        Ascope\Libraries\Processor\ServiceProvider::class,
        Ascope\Libraries\Converter\ServiceProvider::class,
        ValuePad\DAL\Support\DoctrineServiceProvider::class,
		//Clockwork\Support\Laravel\ClockworkServiceProvider::class,
		Maknz\Slack\SlackServiceProvider::class,
		Mews\Purifier\PurifierServiceProvider::class
	],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Input' => Illuminate\Support\Facades\Input::class,
        'Inspiring' => Illuminate\Foundation\Inspiring::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Packages
    |--------------------------------------------------------------------------
    |
    | In order to automate a routine work, it is required to register
    | all the entity packages in the system here.
    */

    'packages' => [
		'Shared',
        'Session',
        'Appraiser',
		'Asc',
        'Company',
        'User',
        'Location',
        'Document',
        'Language',
		'JobType',
		'Customer',
		'Invitation',
		'Appraisal',
		'Log',
		'Payment',
		'Help',
		'Back',
		'Amc',
        'Assignee',
	],

    /*
    |--------------------------------------------------------------------------
    | Support
    |--------------------------------------------------------------------------
    |
    | An array containing support-related information.
    |
    */

    'support' => [
        'email' => env('SUPPORT_EMAIL', 'support@appraisalscope.com'),
    ],

	'secret_license' => env('SECRET_LICENSE'),

	'authorize_net' => [
		'login_id' => env('AUTHORIZE_NET_LOGIN_ID'),
		'transaction_key' => env('AUTHORIZE_NET_TRANSACTION_KEY'),
		'environment' => env('AUTHORIZE_NET_ENVIRONMENT', 'sandbox') === 'sandbox'
			? net\authorize\api\constants\ANetEnvironment::SANDBOX
			: net\authorize\api\constants\ANetEnvironment::PRODUCTION,
		'validation' => env('AUTHORIZE_NET_VALIDATION')
	],

	'front_end_url' => env('FRONT_END_URL'),

	'password_reset_token_lifetime' => 60 * 24, // 1 day

	'push_notifications' => [
		'environment' => env('PUSH_NOTIFICATIONS_ENVIRONMENT') === 'production'
			? Sly\NotificationPusher\PushManager::ENVIRONMENT_PROD
			: Sly\NotificationPusher\PushManager::ENVIRONMENT_DEV,
		'android' => [
			'key' => env('PUSH_NOTIFICATIONS_ANDROID_KEY')
		]
	],

	'geo' => [
		'token' => env('GEO_TOKEN'),
		'enabled' => env('GEO_ENABLED', false)
	],

	'emails_frequency_tracker' => [
		'waiting_time' => 120, // seconds,
		'enabled' => env('EMAILS_FREQUENCY_TRACKER_ENABLED', true)
	],

    'chance' => [
        'max_attempts' => 3,
        'waiting_time' => 2, // minutes
        'handlers' => [
            ValuePad\Push\Support\Processor::TAG => ValuePad\Push\Support\Processor::class
        ]
    ],

    'access_logs' => [
        'enabled' => env('ACCESS_LOGS_ENABLED', false),
        'path' => env('ACCESS_LOGS_PATH', '/var/log/php-fpm')
    ]
];

if (env('APP_DEBUG', false)){
	$config['providers'][] =  ValuePad\Debug\Support\AppServiceProvider::class;
	$config['providers'][] =  ValuePad\Debug\Support\RouteServiceProvider::class;
}

return $config;
