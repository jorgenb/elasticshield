<?php

namespace Jorgenb\OAuthShield;

trait HasElasticsearchIndices
{
    /**
     * Get all the Elasticsearch Indices associated with this user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function indices()
    {
        return $this->hasMany(ElasticIndex::class, 'user_id')->orderBy('created_at', 'desc');
    }
}