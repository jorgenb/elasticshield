<?php

namespace Jorgenb\OAuthShield;

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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'oauthshield');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            $this->commands([
                Console\OAuthShieldIndex::class,
                Console\OAuthShieldUser::class,
            ]);
        }

        // Publish Vue components, Laravel views, SASS project.
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/oauthshield'),
        ], 'oauthshield-views');

        $this->publishes([
            __DIR__.'/../resources/assets/js/components' => base_path('resources/assets/js/components/oauthshield'),
        ], 'oauthshield-components');

        $this->publishes([
            __DIR__.'/../resources/assets/sass' => base_path('resources/assets/sass'),
        ], 'oauthshield-sass');

        $this->publishes([
            __DIR__.'/../tests' => base_path('tests'),
        ], 'oauthshield-tests');


        // Define the scopes available for Passport.
        Passport::tokensCan([
            'head' => 'Perform HEAD requests.'
            'get' => 'Perform HTTP GET requests.',
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
