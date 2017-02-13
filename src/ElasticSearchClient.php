<?php


namespace Jorgenb\OAuthShield;


use Elasticsearch\ClientBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait ElasticSearchClient
{
    /**
     * Configure the Elasticsearch PHP Client and logger
     *
     * @return \Elasticsearch\Client
     */
    public static function make()
    {
        $hosts = [
            env('ELASTICSEARCH_HOST', 'http://localhost:9200')
        ];

        $logger = new Logger('importlog');
        $logger->pushHandler(new StreamHandler(storage_path('logs/elastic.log'), Logger::WARNING));
        $client = ClientBuilder::create()
            ->setLogger($logger)
            ->setHosts($hosts)
            ->build();

        return $client;
    }
}