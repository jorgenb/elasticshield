<?php

namespace Jorgenb\OAuthShield\Http\Controllers;

use Laravel\Passport\Passport;

class OAuthShieldScopeController
{

    /**
     * Get all of the available scopes for the application.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return Passport::scopes();
    }
}
