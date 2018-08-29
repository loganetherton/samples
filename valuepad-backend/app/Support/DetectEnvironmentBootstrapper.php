<?php
namespace ValuePad\Support;

use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use Dotenv;
use RuntimeException;

/**
 * The replacer for the same class provided by Laravel.
 *
 * The default one depends on APP_ENV which is supposed to be set within .env file.
 * However, we need to be able to detect environment from the APP_ENV set from outside (server, cli and etc.).
 * Additionally, we need a single place where the environment can be determined.
 * Therefore, we need first to detect environment then load configurations
 * from the .env file based on the detected environment.
 *
 *
 */
class DetectEnvironmentBootstrapper
{

    /**
     * Bootstrap the given application.
     *
     * @param Application $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $env = $app->detectEnvironment(function () {
            return getenv('APP_ENV') ?: 'local';
        });

        $fn = '.env.' . $env;

        $app->loadEnvironmentFrom(file_exists(base_path($fn)) ? $fn : '.env');

        try {
            Dotenv::load($app->environmentPath(), $app->environmentFile());
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException();
        }
    }
}
