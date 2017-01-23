<?php


namespace Jorgenb\ElasticShield\Facades;

use Illuminate\Support\Facades\Facade;

class ElasticSearchCluster extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'elasticsearchcluster';
    }
}