<?php

namespace Jorgenb\ElasticShield;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class ShieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'elasticshield');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            $this->commands([
                Console\ElasticShieldIndex::class,
                Console\ElasticShieldUser::class,
            ]);
        }

        // Publish Vue components, Laravel views, SASS project.
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/elasticshield'),
        ], 'elasticshield-views');

        $this->publishes([
            __DIR__.'/../resources/assets/js/components' => base_path('resources/assets/js/components/elasticshield'),
        ], 'elasticshield-components');

        $this->publishes([
            __DIR__.'/../resources/assets/sass' => base_path('resources/assets/sass'),
        ], 'elasticshield-sass');

        $this->publishes([
            __DIR__.'/../tests' => base_path('tests'),
        ], 'elasticshield-tests');


        // Define the scopes available for Passport.
        Passport::tokensCan([
            'get' => 'Perform HTTP GET requests',
            'post' => 'Perform HTTP POST requests.',
            'put' => 'Perform HTTP PUT requests.',
            'delete' => 'Perform HTTP DELETE requests.',
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * The ElasticSearchCluster Facade.
         */
        $this->app->bind('elasticsearchcluster', function () {
            return new ElasticSearchCluster();
        });
    }
}
