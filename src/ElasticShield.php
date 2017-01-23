<?php

namespace Jorgenb\ElasticShield;

use Illuminate\Support\Facades\Route;

class ElasticShield {

    /**
     * Get a Passport route registrar.
     *
     * @param  array  $options
     * @return RouteRegistrar
     */
    public static function apiRoutes($callback = null, array $options = [])
    {
        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $options = array_merge($options, [
            'namespace' => '\Jorgenb\ElasticShield\Http\Controllers',
        ]);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    }


    /**
     * Get a Passport route registrar.
     *
     * @param  array  $options
     * @return RouteRegistrar
     */
    public static function frontendRoutes($callback = null, array $options = [])
    {
        $callback = $callback ?: function ($router) {
            $router->forFrontend();
        };

        $options = array_merge($options, [
            'namespace' => '\Jorgenb\ElasticShield\Http\Controllers',
        ]);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    }
}