<?php

namespace Jorgenb\OAuthShield\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Jorgenb\OAuthShield\ElasticIndex;
use Jorgenb\OAuthShield\Facades\ElasticSearchCluster;

class OAuthShieldIndexController extends Controller
{
    /**
     * Return all indices for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $request->user()->indices()->get(['id', 'name', 'created_at', 'updated_at']);
    }

    /**
     * Create a new Elasticsearch Index in the cluster.
     * Store a new Elasticsearch index in the database.
     *
     * @param Request $request
     * @return Collection|string
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:elastic_indices|min:2|max:255|alpha_num',
        ]);

        ElasticSearchCluster::create($request->get('name'));

        return $request->user()->indices()->create(['name' => $request->get('name')]);
    }

    /**
     * Delete an Elasticsearch index from storage and the Elasticsearch cluster.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $index = $request->user()->indices->find($id);

        if (is_null($index)) {
            return new Response(['message' => 'Not found'], 404);
        }

        ElasticSearchCluster::destroy($index->name);
        ElasticIndex::destroy($id);

        return new Response(['acknowledged' => true], 200);
    }
}
