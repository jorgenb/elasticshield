<?php

namespace Jorgenb\ElasticShield\Http\Controllers;

use Laravel\Passport\Passport;

class ElasticShieldScopeController
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
