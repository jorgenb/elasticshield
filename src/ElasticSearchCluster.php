<?php


namespace Jorgenb\ElasticShield;


class ElasticSearchCluster
{
    /**
     * The Elasticsearch PHP Client.
     *
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * ElasticSearchCluster constructor.
     */
    public function __construct()
    {
        $this->client = ElasticSearchClient::make();
    }

    /**
     * Create an Elasticsearch index in the cluster
     * with the default number of shards and replicas.
     *
     * @param $elasticIndex
     * @return array|\HttpException
     */
    public function create($elasticIndex)
    {
        $params = [
            'index' => $elasticIndex,
            'body' => [
                'settings' => [
                    'number_of_shards' => env('NUMBER_OF_SHARDS', 5),
                    'number_of_replicas' => env('NUMBER_OF_REPLICAS', 1)
                ]
            ]
        ];

        try {
            return  $this->client->indices()->create($params);
        } catch (\Exception $e) {
            return $this->abort($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Delete an Elasticsearch index in the cluster.
     *
     * @param $elasticIndex
     * @return array|\HttpException
     */
    public function destroy($elasticIndex)
    {
        $params = ['index' => $elasticIndex];

        try {
            return $this->client->indices()->delete($params);
        } catch (\Exception $e) {
            return $this->abort($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Get global stats from the Elasticsearch cluster.
     *
     * @return array|\HttpException
     */
    public function clusterStats()
    {
        try {
            return $this->client->cluster()->stats();
        } catch (\Exception $e) {
            return $this->abort($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Get per index stats from the Elasticsearch cluster.
     *
     * @param $elasticIndex
     * @return array|\HttpException
     */
    public function indexStats($elasticIndex)
    {
        $params = [
            'index' => $elasticIndex,
        ];

        try {
            return $this->client->indices()->stats($params);
        } catch (\Exception $e) {
            return $this->abort($e->getCode(), $e->getMessage());
        }
    }


    /**
     * Get the error message returned by Elasticsearch and abort.
     *
     * @param $code
     * @param $error
     *
     * @return \HttpException
     */
    private function abort($code, $error)
    {
        function isJson($string) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }

        if (is_null($code) || $code === 0) {
            $code = 400;
        }

        if (isJson($error)) {
            $reason = collect(json_decode($error))->get('error')->reason;
            return abort($code, $reason);
        }

        if (is_null($error)) {
            return abort(400, 'Bad request');
        }

        if (is_string($error)) {
            return abort($code, $error);
        }
    }
}