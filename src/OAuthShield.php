<?php

namespace Jorgenb\OAuthShield;

use Illuminate\Support\Facades\Route;

class OAuthShield {

    /**
     * Get an OAuth Shield route registrar for all things API.
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
            'namespace' => '\Jorgenb\OAuthShield\Http\Controllers',
        ]);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    }


    /**
     * Get an OAuth Shield route registrar for all things frontend.
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
            'namespace' => '\Jorgenb\OAuthShield\Http\Controllers',
        ]);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    }
}