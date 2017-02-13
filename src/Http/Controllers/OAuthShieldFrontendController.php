<?php

namespace Jorgenb\OAuthShield\Http\Controllers;

use App\Http\Controllers\Controller;
use Jorgenb\OAuthShield\Facades\ElasticSearchCluster;

class OAuthShieldFrontendController extends Controller
{
    public function welcome()
    {
        return view('oauthshield::welcome');
    }

    public function home()
    {
        return view('oauthshield::home');
    }

    public function docs()
    {
        return view('oauthshield::documentation');
    }

    public function stats()
    {
        return ElasticSearchCluster::clusterStats();
    }
}
