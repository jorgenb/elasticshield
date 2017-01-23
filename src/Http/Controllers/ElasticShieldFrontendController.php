<?php

namespace Jorgenb\ElasticShield\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Jorgenb\ElasticShield\Facades\ElasticSearchCluster;

class ElasticShieldFrontendController extends Controller
{
    public function welcome()
    {
        return view('elasticshield::welcome');
    }

    public function home()
    {
        return view('elasticshield::home');
    }

    public function docs()
    {
        return view('elasticshield::documentation');
    }

    public function stats()
    {
        return ElasticSearchCluster::clusterStats();
    }
}
