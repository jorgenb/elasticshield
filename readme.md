> TIP! Read this entire page before proceeding with setup.

> PLEASE NOTE! This package is work in progress and should not be considered production ready.

# OAuth Shield

OAuth Shield is a package for [Laravel](https://www.laravel.com) that includes an
[OAuth 2.0 Server](https://github.com/thephpleague/oauth2-server) that allows you to create,
manage and protect your [Elasticsearch](https://www.elastic.co/) indices using a web frontend and/or a JSON api.
In a nutshell, OAuth Shield allows you to exchange an
[Personal Access Token](https://tools.ietf.org/html/rfc6749#section-1.4) for a
[JWT token](https://tools.ietf.org/html/rfc7519) that will allow or deny access to a specific Elasticsearch
API endpoint.

Features:

- Simplified authentication process using long lived Personal Access Tokens.
- Rate limiting on authentication attempts.
- Tie Oauth scopes as HTTP request methods (e.g. 'GET', 'PUT', 'POST', 'DELETE', 'HEAD'') for your token. 
- Client authenticates once and then get’s a JWT token that expires after a set time.
- A user can have one or more indices protected by a token.
- Elastic shield JSON Api for managing indices and tokens.
- Self service web fronted for managing indices and tokens.

In addition to this application you will need to install and configure a HTTP server that can handle the authorization
part of protecting your Elasticsearch indices. This server must be capable of supporting
[LuaJIT](http://openresty.org/en/luajit.html).

On this page you will find examples on how to configure an [OpenResty](http://openresty.org/) server for this purpose.

![OAuth Shield demo](oauth_shield_demo.gif?raw=true "OAuth Shield frontend demo.")

## Installing and configuring OAuth Shield

Create a new [Laravel](https://laravel.com/) 5.4 application and make sure to configure a database,
an application key and pull in all the dependencies listed in your `package.json` file:

```bash
laravel new oauthshield
```

Navigate to your directory where you installed Laravel and run `yarn`:

```bash
yarn
```

Proceed with configuring a database connection.

For more information on creating, configuring and serving a Laravel application please see: https://laravel.com/docs

Install the OAuth Shield package by adding it to your `composer.json` file:

```bash
   "require": {
        "php": ">=5.6.4",
        "laravel/framework": "5.4.*",
        "laravel/tinker": "~1.0",
        "jorgenb/oauthshield": "v0.1.3"
    },
```

Make sure you run PHP Composer to install it:

```
composer update
```

Once installed, register the OAuth Shield and Laravel Passport service providers in the `providers` array of your `config/app.php`
configuration file:

```php
Laravel\Passport\PassportServiceProvider::class,
Jorgenb\OAuthShield\ShieldServiceProvider::class,
```

Register the OAuth Shield Cluster facade in the `aliases` array of your `config/app.php` configuration file:

```php
'ElasticSearchCluster' => \Jorgenb\OAuthShield\Facades\ElasticSearchCluster::class,
```

The OAuth Shield and Laravel Passport service provider registers its own database migration directory with Laravel,
so you should migrate your database after registering the provider.

> If you are using MariaDB you most likely need to alter the default string length that Laravel uses.
> Do this by editing your `AppServiceProvider.php` file located in `app/Providers` and add:
> 
> ```
> public function boot()
> {
>   Schema::defaultStringLength(191);
> }
> ```
> See: https://laravel.com/docs/5.4/releases for more information.

The OAuth Shield migrations will create a table
to store the names of your Elasticsearch indices. The Passport migrations will create the tables that OAuth Shield
needs to store clients and access tokens:


``` 
php artisan migrate
```

Next, you should run the `passport:install` command. This command will create the encryption keys needed to generate
secure access tokens. In addition, the command will create "personal access" and "password grant" clients
which will be used to generate access tokens:

```
php artisan passport:install
```

Add the `Laravel\Passport\HasApiTokens` and the `Jorgenb\OAuthShield\HasElasticSearchIndices`
traits to your `App\User` model.

```php
<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Jorgenb\OAuthShield\HasElasticsearchIndices;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasElasticsearchIndices;
}
```

Change the Api authentication guard to use the Laravel Passport driver in the `aliases` array of your
`config/auth.php` configuration file:

```php
'guards' => [
        // ... other guards
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],
```

Change the default name of the Laravel application in the `app.php` file located at `config/app.php`:

```
'name' => 'OAuth Shield',
```

Finally add the OAuth Shield API routes to your `AuthServiceProvider.php` file:

```php
public function boot()
{
  OAuthShield::apiRoutes();
}
```

### .env

Add the following variables to your `.env` file.

The full HTTP address of your Openresty server:
```
OPENRESTY_SERVER=http://localhost:8080
```

The private key used to sign your JWT tokens. Generate a random 32 character string.
```
JWT_PRIVATE_KEY=example_key
```

The full HTTP address of your Elasticsearch node or cluster:
```
ELASTICSEARCH_HOST=http://localhost:9200
```

### Create an admin user

Use Artisan to create a new admin user:

```
php artisan oauthshield:user --admin
```

This command will make a new user in your database along with a Personal Access Token that will have full
access to your Elasticsearch cluster.

**Please store this token in a safe place since this is the only time that it will be displayed**.

## Installing and configuring the OAuth Shield Frontend

You can use the following pre-built Vue components as a frontend for the OAuth Shield API.

To publish the components use the `vendor:publish` Artisan command:

```shell
php artisan vendor:publish --tag=oauthshield-components
```

The published components will be placed in your `resources/assets/js/components/oauthshield` directory.
Once the components have been published, you should register them in your `resources/assets/js/app.js` file:

```javascript
Vue.component(
    'personal-access-tokens',
    require('./components/oauthshield/PersonalAccessTokens.vue')
);

Vue.component(
    'elasticsearch-indices',
    require('./components/oauthshield/ElasticsearchIndices.vue')
);

Vue.component(
    'cluster-stats',
    require('./components/oauthshield/ClusterStats.vue')
);
```

After registering the components, make sure you run `npm run dev` to recompile all your assets (in previous versions of 
Laravel you would use `gulp` to do this).

```shell
npm run dev
```

You can either drop these components into your own frontend or use the bundled OAuth Shield frontend.
OAuth Shield uses the Bulma CSS Framework and you can publish the SASS project files by running:

```
php artisan vendor:publish --tag=oauthshield-sass
```

The SASS project files will be placed in your `resources/assets/sass` directory.

In addition to this you need to pull in some required packages. You can add the required packages by
running the `yarn` command:

```
yarn add vue-resource bulma font-awesome nprogress
```

If your system does not have `yarn` installed you can use `npm install` instead.

Add the following to your `webpack.mix.js` file.

```javascript
mix.js('resources/assets/js/app.js', 'public/js')
    .copy('node_modules/font-awesome/fonts', 'public/fonts')
    .sass('resources/assets/sass/bulma.scss', 'public/css/app.css');
```

You also need to import `Nprogress` and `vue-resource` into your Javascript framework. Add the following to
your `bootstrap.js` file:

```javascript
window.Vue = require('vue');
require('vue-resource');
window.NProgress = require('nprogress');
```

In order for the frontend to consume it's own API you will need to configure
it to send a Laravel CSRF token with every request.

Add the following to your `bootstrap.js` file located in the `resources/assets/js directory`:

```javascript
/**
 * We'll register a HTTP interceptor to attach the "CSRF" header to each of
 * the outgoing requests issued by this application. The CSRF middleware
 * included with Laravel will automatically verify the header's value.
 * 
 * We also call NProgress to display a slim progress bar in the UI for each AJAX request.
 */

 Vue.http.interceptors.push((request, next) => {
     request.headers.set('X-CSRF-TOKEN', Laravel.csrfToken);
     NProgress.start();
     next(function (response) {
         if (response.status === 200) {
             NProgress.done();
         }
     });
 });
```

Add the `CreateFreshApiToken` middleware to your `Kernel.php` file located in the `app/Http` directory:

```php
'web' => [
    // Other middleware...
    \Laravel\Passport\Http\Middleware\CreateFreshApiToken::class,
],
```

Make sure you run `npm run dev` to recompile all your assets (in previous versions of Laravel you would use
`gulp` to do this).

```shell
npm run dev
```

Finally add the OAuth Shield frontend routes to your `AuthServiceProvider.php` file:

```php
public function boot()
{
  OAuthShield::frontendRoutes();
}
```

This version of OAuth Shield does not ship with a frontend for handling logins.
If you quickly want to test all features of the frontend you can circumvent this
limitation by manually logging a user into Laravel. Add the following code to
your `web.php` file located in your `routes` directory:

```php
use Illuminate\Support\Facades\Auth;

Auth::loginUsingId(1);
```

You should now be able to access the Oauth Shield frontend were you have configured your Laravel application. E. g.:

http://localhost/oauthshield

For more information on serving a Laravel application through a HTTP server please refer to https://laravel.com/docs

You can use Artisan to inspect which routes are available through OAuth Shield:

```
php artisan route:list
```

### Testing OAuth Shield

Tests can be published to your Laravel project by running:

```shell
php artisan vendor:publish --tag=oauthshield-tests
```

This will publish the OAuthShieldTestApi.php file to your `tests/Feature` directory.
This file performs a series of HTTP tests against the OAuth Shield JSON Api.

Make sure to update your `TestCase.php` file located in the `tests` directory to reflect your environment.

## JSON Api
Elastic Shield includes a JSON API for managing Elasticsearch indices and personal access tokens.
Below you can find a review of all the API routes.

### Checking scopes

Add the following middleware to the `$routeMiddleware` property of your `app/Http/Kernel.php` file:

```
'scopes' => \Laravel\Passport\Http\Middleware\CheckScopes::class,
'scope' => \Laravel\Passport\Http\Middleware\CheckForAnyScope::class,
```

Standard CURL syntax is used when demonstrating making HTTP requests to the endpoints.

### Indices operations
---
`GET /api/shield/indices`

This route returns any indices that the user has created:
```
curl --request GET \
  --include \
  --url http://localhost/api/oauthshield/indices \
  --header 'accept: application/json' \
  --header 'cache-control: no-cache' \
  --header 'authorization: Bearer ...'
```

---
`POST /api/shield/indices`

Create an Elasticsearch Index with the default settings configured in this API:
```
curl --request POST \
  --include \
  --url http://localhost/api/oauthshield/indices \
  --data '{ "name": "..." }' \
  --header 'accept: application/json' \
  --header 'content-type: application/json' \
  --header 'cache-control: no-cache' \
  --header 'authorization: Bearer ...'
```
---
`DELETE /api/shield/indices/{index-id}`

Delete an Elasticsearch Index:
```
curl --request DELETE \
  --include \
  --url http://localhost/api/oauthshield/indices/123 \
  --header 'accept: application/json' \
  --header 'content-type: application/json' \
  --header 'cache-control: no-cache' \
  --header 'authorization: Bearer ...'
```

### OAuth 2.0 operations
---
`GET /api/oauth/scopes`

This route returns all of the scopes defined. You may use this route to list the scopes a user may assign to a personal
access token:
```
curl --request GET \
  --include \
  --url http://localhost/api/oauth/scopes \
  --header 'accept: application/json' \
  --header 'cache-control: no-cache' \
  --header 'authorization: Bearer ...'
```  
---
`GET /api/oauth/personal-access-tokens`

This route returns all of the personal access tokens that the authenticated user has created.
This is primarily useful for listing all of the user's tokens so that they may edit or delete them:
```
curl --request GET \
  --include \
  --url http://localhost/api/oauth/personal-access-tokens \
  --header 'accept: application/json' \
  --header 'cache-control: no-cache' \
  --header 'authorization: Bearer ...'
```  
---
`POST /api/oauth/personal-access-tokens`

This route creates a new personal access token. Personal Access Tokens are per default long lived.
It requires two pieces of data: the token's name and the scopes that should be assigned to the token:
```
curl --request POST \
  --include \
  --url http://localhost/api/oauth/personal-access-tokens \
  --data '{"name": "...","scopes": ["..."]}' \
  --header 'accept: application/json' \
  --header 'content-type: application/json' \
  --header 'cache-control: no-cache' \
  --header 'authorization: Bearer ...'
```
---
`DELETE /api/oauth/personal-access-tokens/{token-id}`

This route may be used to delete personal access tokens:
```
curl --request DELETE \
  --include \
  --url http://localhost/api/oauth/personal-access-tokens/123 \
  --header 'accept: application/json' \
  --header 'cache-control: no-cache' \
  --header 'content-type: application/json' \
  --header 'authorization: Bearer ...'
```

## Openresty

Please refer to http://openresty.org/ for documentation related to setting up an `OpenResty` server for your environment.

### lua-resty-jwt and lua-resty-hmac

Grab these LUA-scripts and add them to your Openresty configuration:

1. https://github.com/SkyLothar/lua-resty-jwt
2. https://github.com/jkeys089/lua-resty-hmac

```
lua_package_path "/path/to/lua-resty-jwt/lib/?.lua;/path/to/lua-resty-hmac/lib/?.lua;;";
```

### Installing and configuring Openresty

I have included the following `Nginx` configuration and `LUA` script which you may use as inspiration on how to
configure and manage your `OpenResty` installation:

`nginx.conf`:

```nginx
worker_processes  1;

error_log logs/lua.log;
error_log logs/lua.log notice;
error_log logs/lua.log info;

events {
  worker_connections 1024;
}

http {
  # Path to additional LUA-scripts.
  lua_package_path "/path/to/lua-resty-jwt/lib/?.lua;/path/to/lua-resty-hmac/lib/?.lua;;";

  # The Elasticsearch node or cluster.
  upstream elasticsearch {
    server 127.0.0.1:9200;
    keepalive 15;
  }

server {
  listen 8080;
  
  client_body_buffer_size 10M;
  
  # REMEMBER! Protect this endpoint from the world. Only localhost should be allowed access to this route.
  location /_token { proxy_pass http://localhost/api/token; }

  # Your Elasticsearch node or nodes.
  location @elasticsearch {
        proxy_pass http://localhost:9200;
        proxy_redirect off;
        proxy_buffering off;
        proxy_http_version 1.1;
        proxy_set_header Connection "Keep-Alive";
        proxy_set_header Proxy-Connection "Keep-Alive";
  }

  location / {
    # The private key used to sign your JWT tokens. Should match the one you use for OAuth Shield
    set $jwt_secret "example_key";
    
    # Use the access_by_lua_file directive or similiar to parse the request before Nginx renders.
    access_by_lua_file /full/path/to/oauthshield.lua;
    
    # Send the request to Elasticsearch as you would normally
    proxy_pass http://elasticsearch;
    }
  }
}
```

`oauthshield.lua`:

```lua
local jwt = require "resty.jwt"
local cjson = require "cjson"
local authHeader = ngx.req.get_headers()["Authorization"]
local uri = ngx.var.uri
local uri_resource = uri:match('^%/(%w+)') -- Capture all alphanumeric characters after the first slash.
local method = ngx.req.get_method()

jwt_token = ngx.var.cookie_jwt -- global

-- If header is not set or string is empty flat out deny the request.
if not authHeader or authHeader == '' then
  ngx.status = ngx.HTTP_FORBIDDEN
  ngx.header.content_type = "application/json; charset=utf-8"
  ngx.say(cjson.encode({ error = "No authorization header provided." }))
  ngx.exit(ngx.HTTP_OK)
end

-- Check if client sent a token. If not, authenticate and get token based on credentials.
if not jwt_token then

	-- Send syncronous request to Oauth Server in order to determine if:
	-- 1) Token can authenticate.
	-- 2) The requested Elasticsearch index is valid for this token.
	local response = ngx.location.capture("/_token/" .. uri_resource)

	-- is client rate limited?
        if response.status == 429 then
		ngx.status = ngx.HTTP_FORBIDDEN
		ngx.header.content_type = "application/json; charset=utf-8"
    	ngx.say(cjson.encode({ error = "Too Many Authentication Attempts." }))
    	ngx.exit(ngx.HTTP_OK)
	end

 	-- kill all other invalid responses immediately
	if response.status ~= 200 then
		ngx.status = ngx.HTTP_UNAUTHORIZED
		ngx.header.content_type = "application/json; charset=utf-8"
    	ngx.say(cjson.encode({ error = "Unauthorized." }))
    	ngx.exit(ngx.HTTP_OK)
	end

	-- Overwrite global var in order to avoid client having to resend request.
	jwt_token = response.body

	-- Set cookie and make _sure_ to expire it before the token expires.
	ngx.header['Set-Cookie'] = "jwt=" .. response.body .. "; Expires=" .. ngx.cookie_time(ngx.time()+3600) .. ";" 
end

-- Check that token provided by the client is valid using private key.
-- More info at: https://tools.ietf.org/html/rfc7519#section-4.1.3
local jwt_obj = jwt:verify(ngx.var.jwt_secret, jwt_token, 0)
if not jwt_obj["verified"] then
	ngx.status = ngx.HTTP_UNAUTHORIZED
   	ngx.header.content_type = "application/json; charset=utf-8"
   	ngx.say(cjson.encode({ error = "Error decoding token." }))
   	ngx.exit(ngx.HTTP_OK)
end	

-- Get the allowed scopes for this JWT token.
local user = jwt_obj.payload.sub
local scopes = jwt_obj.payload.scopes

-- Check that JWT aud property matches the requested uri resource
local a = string.match(uri_resource, jwt_obj.payload.aud)
if not a then
	ngx.log(ngx.WARN, "Audience properties do not match. User ["..user.."] not allowed to access the resource ["..method.." "..uri.."]")
    ngx.status = ngx.HTTP_FORBIDDEN
    ngx.header.content_type = "application/json; charset=utf-8"
    ngx.say(cjson.encode({ error = "You don't have access to this resource." }))
    ngx.exit(ngx.HTTP_OK)
end

-- Define all Elasticsearch API routes and methods.
local allowed  = false
local restrictions = {
    ["^/$"]                             = { "GET", "HEAD" },
    ["^/?[^/]*/?[^/]*/_search"]         = { "GET", "POST" },
    ["^/?[^/]*/?[^/]*/_msearch"]        = { "GET", "POST" },
    ["^/?[^/]*/?[^/]*/_validate/query"] = { "GET", "POST" },
    ["/_cluster.*"]                     = { "GET" },
    ["^/?[^/]*/?[^/]*/_bulk"]           = { "GET", "POST" },
    ["^/?[^/]*/?[^/]*/_all"]            = { "GET", "POST" },
    ["^/?[^/]*/?[^/]*/_refresh"]        = { "GET", "POST" },
    ["^/?[^/]*/?[^/]*/?[^/]*/_create"]  = { "GET", "POST" },
    ["^/?[^/]*/?[^/]*/?[^/]*/_update"]  = { "GET", "POST" },
    ["^/?[^/]*/?[^/]*/?.*"]             = { "GET", "POST", "PUT", "DELETE", "HEAD" },
    ["^/?[^/]*/?[^/]*$"]                = { "GET", "POST", "PUT", "DELETE", "HEAD" },
    ["/_aliases"]                       = { "GET", "POST" }
}

-- Check that scope matches method.
local s = nil
for i, scope in pairs(scopes) do
	if scope == string.lower(method) then
		s = true
	end
end

for path, methods in pairs(restrictions) do

  -- path matched rules?
  local p = string.match(uri, path)

  local m = nil

  -- method matched rules?
  for _, _method in pairs(methods) do
    m = m and m or string.match(method, _method)
  end

  -- path, method and scope?
  if p and m and s then
    allowed = true
  end
end

if not allowed then
    ngx.log(ngx.WARN, "User ["..user.."] not allowed to access the resource ["..method.." "..uri.."]")
    ngx.status = ngx.HTTP_FORBIDDEN
    ngx.header.content_type = "application/json; charset=utf-8"
    ngx.say(cjson.encode({ error = "You don't have access to this resource." }))
    ngx.exit(ngx.HTTP_OK)
else
    ngx.log(ngx.INFO, "User ["..user.."] accessing resource ["..method.." "..uri.."]")
end
```

## TODO

- Make test case for Openresty.
- Implement admin access to OAuth Shield.

## Other resources:

- https://laravel.com/docs/5.3/passport
- https://www.elastic.co/blog/playing-http-tricks-nginx
- https://gist.github.com/karmi/b0a9b4c111ed3023a52d#file-authorize-lua
- https://github.com/SkyLothar/lua-resty-jwt
- https://github.com/jkeys089/lua-resty-hmac

