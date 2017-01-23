@extends('elasticshield::layouts.app')

@section('content')
    <section class="hero is-medium is-primary is-bold">
        <div class="hero-body">
            <div class="container">
                <div class="columns">
                    <div class="column is-two-thirds-desktop is-half-tablet">
                        <h1 class="title">
                            <span class="icon is-large">
                                <i class="fa fa-shield" aria-hidden="true"></i>
                            </span>
                            Elasticshield {{env('RELEASE')}}
                        </h1>
                        <h2 class="subtitle">
                            Create and manage your Elasticsearch Indices
                        </h2>
                    </div>
                    <div class="column">
                        <cluster-stats></cluster-stats>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
