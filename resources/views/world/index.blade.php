@extends('world.layout')

@section('title') Home @endsection

@section('content')
{!! breadcrumbs(['Encyclopedia' => 'world']) !!}

<h1>World</h1>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body text-center">
                <img src="{{ asset('images/characters.png') }}" />
                <h5 class="card-title">Characters</h5>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><a href="{{ url('world/species') }}">Species</a></li>
				<li class="list-group-item"><a href="{{ url('world/subtypes') }}">Subtypes</a></li>
                <li class="list-group-item"><a href="{{ url('world/rarities') }}">Rarities</a></li>
                <li class="list-group-item"><a href="{{ url('world/trait-categories') }}">Trait Categories</a></li>
                <li class="list-group-item"><a href="{{ url('world/traits') }}">All Traits</a></li>
                <li class="list-group-item"><a href="{{ url('world/character-categories') }}">Character Categories</a></li>
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body text-center">
                <img src="{{ asset('images/inventory.png') }}" />
                <h5 class="card-title">Items & Awards</h5>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><a href="{{ url('world/item-categories') }}">Item Categories</a></li>
                <li class="list-group-item"><a href="{{ url('world/items') }}">All Items</a></li>
                <li class="list-group-item"><a href="{{ url('world/award-categories') }}">Award Categories</a></li>
                <li class="list-group-item"><a href="{{ url('world/awards') }}">All Awards</a></li>
                <li class="list-group-item"><a href="{{ url('world/currencies') }}">Currencies</a></li>
                <li class="list-group-item"><a href="{{ url('world/recipes') }}">All Recipes</a></li>
            </ul>
        </div>
    </div>
</div>
@endsection
