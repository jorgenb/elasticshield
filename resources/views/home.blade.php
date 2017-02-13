@extends('oauthshield::layouts.app')

@section('content')
    <section class="section">
        <div class="container">
            <personal-access-tokens></personal-access-tokens>
            <br>
            <elasticsearch-indices></elasticsearch-indices>
        </div>
    </section>
@endsection
