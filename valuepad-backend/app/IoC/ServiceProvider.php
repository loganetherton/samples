<?php

namespace ValuePad\IoC;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * @var array
     */
    private $resolvedFactories = [];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFactories();
        $this->registerImplementations();
        $this->registerInitializers();
    }

    /**
     * Registers initializers
     */
    public function registerInitializers()
    {
        $initializers = Config::get('ioc.initializers', []);

        foreach ($initializers as $abstract => $initializer) {

            $callback = function ($object) use($initializer) {
                call_user_func(new $initializer(), $object, $this->app);
            };

            if (is_string($abstract)) {
                $this->app->resolving($abstract, $callback);
            } else {
                $this->app->resolving($callback);
            }
        }
    }

    /**
     * Registers factories
     */
    public function registerFactories()
    {
        $factories = Config::get('ioc.factories', []);

        foreach ($factories as $abstract => $factory) {
            $this->app->bind($abstract, function (Container $container, array $parameters = []) use($factory, $abstract) {
                $caller = array_take($parameters, 'caller');
                unset($parameters['caller']);

                $class = is_string($caller) ? $caller : get_class($caller);

                $key = $abstract . ':' . $class;

                if (! array_has($this->resolvedFactories, $key)) {
                    $context = new Context();
                    $context->setAbstract($abstract);
                    $context->setCaller($caller);
                    $context->setParameters(array_values($parameters));
                    $this->resolvedFactories[$key] = call_user_func(new $factory(), $container, $context);
                }

                return $this->resolvedFactories[$key];
            });
        }
    }

    /**
     * Binds all implementations
     */
    private function registerImplementations()
    {
        $implementations = Config::get('ioc.implementations', []);

        foreach ($implementations as $interface => $implementation) {
            $this->app->singleton($interface, $implementation);
        }
    }
}