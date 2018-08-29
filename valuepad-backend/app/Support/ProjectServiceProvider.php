<?php
namespace ValuePad\Support;

use Ascope\Libraries\Support\Interfaces\ContainerAwareInterface;
use Illuminate\Support\ServiceProvider;

/**
 * The service provider where you can register what you need to be available across the project.
 *
 *
 */
class ProjectServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /**
         * Sets container for instances that need it
         */
        $this->app->resolving(function (ContainerAwareInterface $object) {
            $object->setContainer($this->app);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AfterPartyMiddleware::class, AfterPartyMiddleware::class);
    }
}
