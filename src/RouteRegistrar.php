<?php

namespace Jorgenb\ElasticShield;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RouteRegistrar
{
    /**
     * The router implementation.
     *
     * @var Router
     */
    protected $router;

    /**
     * Create a new route registrar instance.
     *
     * @param  Router  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Register routes for transient tokens, clients, and personal access tokens.
     *
     * @return void
     */
    public function all()
    {
        $this->forPersonalAccessTokens();
        $this->forElasticIndices();
        $this->forJwtToken();
    }

    /**
     * Register the route needed for issuing JWT tokens.
     */
    public function forJwtToken()
    {
        $this->router->group(['middleware' => ['api', 'auth:api']], function ($router) {
            $router->get('api/token/{elasticIndex}', function (Request $request, $elasticIndex) {
                // Get a collection of the indices associated with this user.
                $indices = $request->user()->indices->pluck('name');

                //Deny the request if the requested Elasticsearch index does not exist in the collection.
                if (!$indices->contains($elasticIndex)) {
                    return new Response('Forbidden.', 401);
                }

                //Create a JWT token that will ultimately be sent to the client via Nginx.
                $key = env('JWT_PRIVATE_KEY');
                $token = array(
                    "iss" => env('APP_URL'),
                    "sub" => $request->user()->email,
                    "aud" => $elasticIndex,
                    "iat" => Carbon::now()->timestamp,
                    "nbf" => Carbon::now()->timestamp,
                    'exp' => Carbon::now()->addMinutes(2)->timestamp,
                    'scopes' => $request->user()->token()->scopes, // kinda funny to have an array in a jwt token :)
                );

                return JWT::encode($token, $key);
            });
        });
    }

    /**
     * Register routes needed for the Frontend GUI.
     */
    public function forFrontend()
    {
        $this->router->group(['middleware' => ['web']], function ($router) {
            $router->get('/elasticshield', [
                'uses' => 'ElasticShieldFrontendController@welcome',
            ]);
            $router->get('/elasticshield/stats', [
                'uses' => 'ElasticShieldFrontendController@stats',
            ]);
        });

        $this->router->group(['middleware' => ['web', 'auth']], function ($router) {
            $router->get('/elasticshield/home', [
                'uses' => 'ElasticShieldFrontendController@home',
            ]);
        });
    }

    /**
     * Register the routes needed for managing Elasticsearch Indices.
     */
    public function forElasticIndices()
    {
        // Routes for Vue components.
        $this->router->group(['middleware' => ['web', 'auth']], function ($router) {
            $router->resource('elasticshield/indices', 'ElasticShieldIndexController', ['except' => ['show', 'edit', 'update', 'create']]);
        });
        // Api routes
        $this->router->group(['middleware' => ['api', 'auth:api']], function ($router) {
            $router->resource('api/elasticshield/indices', 'ElasticShieldIndexController', ['except' => ['show', 'edit', 'update', 'create']]);
        });
    }

    /**
     * Register the routes needed for managing personal access tokens.
     *
     * @return void
     */
    public function forPersonalAccessTokens()
    {
        $this->router->group(['middleware' => ['web', 'auth']], function ($router) {
            $router->get('/oauth/scopes', [
                'uses' => 'ElasticShieldScopeController@all',
            ]);

            $router->get('/oauth/personal-access-tokens', [
                'uses' => 'ElasticShieldPersonalAccessTokenController@forUser',
            ]);

            $router->post('/oauth/personal-access-tokens', [
                'uses' => 'ElasticShieldPersonalAccessTokenController@store',
            ]);

            $router->delete('/oauth/personal-access-tokens/{token_id}', [
                'uses' => 'ElasticShieldPersonalAccessTokenController@destroy',
            ]);
        });


        $this->router->group(['middleware' => ['api', 'auth:api']], function ($router) {

            $router->get('/api/oauth/scopes', [
                'uses' => 'ElasticShieldScopeController@all',
            ]);

            $router->get('/api/oauth/personal-access-tokens', [
                'uses' => 'ElasticShieldPersonalAccessTokenController@forUser',
            ]);

            $router->post('/api/oauth/personal-access-tokens', [
                'uses' => 'ElasticShieldPersonalAccessTokenController@store',
            ]);

            $router->delete('/api/oauth/personal-access-tokens/{token_id}', [
                'uses' => 'ElasticShieldPersonalAccessTokenController@destroy',
            ]);
        });
    }
}
