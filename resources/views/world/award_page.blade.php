@extends('world.layout')

@section('title') {{ $award->name }} @endsection

@section('meta-img') {{ $imageUrl }} @endsection

@section('meta-desc') 
@if(isset($award->category) && $award->category) <p><strong>Category:</strong> {{ $award->category->name }}</p> @endif
@if(isset($award->rarity) && $award->rarity) :: <p><strong>Rarity:</strong> {{ $award->rarity }}: {{ $award->rarityName }}</p> @endif
 :: {!! substr(str_replace('"','&#39;',$award->description),0,69) !!}
@if(isset($award->uses) && $award->uses) :: <p><strong>Uses:</strong> {!! $award->uses !!}</p> @endif
@endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Awards' => 'world/awards', $award->name => $award->idUrl]) !!}

<div class="row">
    <div class="col-sm">
    </div>
    <div class="col-lg-6 col-lg-10">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row world-entry">
                    @if($imageUrl)
                        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" /></a></div>
                    @endif
                    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
                        <h1>{!! $name !!}</h1>
                        <div class="row">
                            @if(isset($award->category) && $award->category)
                                <div class="col">
                                    <p><strong>Category:</strong> {{ $award->category->name }}</p>
                                </div>
                            @endif
                            @if(isset($award->rarity) && $award->rarity)
                                <div class="col">
                                    <p><strong>Rarity:</strong> {{ $award->rarity }}: {{ $award->rarityName }}</p>
                                </div>
                            @endif
                            @if(isset($award->artist) && $award->artist)
                                <div class="col">
                                    <p><strong>Artist:</strong> {!! $award->artist !!}</p>
                                </div>
                            @endif
                            <div class="col-md-5 col-md">
                                <div class="row">
                                    @foreach($award->tags as $tag)
                                        @if($tag->is_active)
                                        <div class="col">
                                            {!! $tag->displayTag !!}
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="world-entry-text">
                            @if(isset($award->reference_url) && $award->reference_url)  <p><strong>Wiki Link:</strong> <a href="{{ $award->reference_url }}">{{ $award->reference_url }}</a></p> @endif
                            {!! $description !!}
                            @if(isset($award->uses) && $award->uses || isset($award->source) && $award->source || isset($award->data['shops']) && $award->data['shops'] || isset($award->data['prompts']) && $award->data['prompts'])
                            
                                @if(isset($award->uses) && $award->uses)  <p><strong>Uses:</strong> {!! $award->uses !!}</p> @endif
                                @if(isset($award->source) && $award->source || isset($award->data['shops']) && $award->data['shops'] || isset($award->data['prompts']) && $award->data['prompts'])
                                <h5>Availability</h5>
                                <div class="row">
                                    @if(isset($award->data['release']) && $award->data['release'])
                                        <div class="col">
                                            <p><strong>Source:</strong></p> 
                                            <p>{!! $award->data['release'] !!}</p>
                                        </div>
                                    @endif
                                    @if(isset($award->data['shops']) && $award->data['shops'])
                                        <div class="col">
                                            <p><strong>Purchaseable At:</strong></p>
                                                <div class="row">
                                                    @foreach($award->shops as $shop) <span class="badge" style="font-size:95%; background-color: #fefcf6; margin:5px;"><a href="{{ $shop->url }}">{{ $shop->name }}</a></span>
                                                    @endforeach
                                                </div>
                                        </div>
                                    @endif
                                    @if(isset($award->data['prompts']) && $award->data['prompts'])
                                        <div class="col">
                                            <p><strong>Drops From:</strong></p>
                                                <div class="row">
                                                    @foreach($award->prompts as $prompt) <span class="badge" style="font-size:95%; background-color: #fefcf6; margin:5px;"><a href="{{ $prompt->url }}">{{ $prompt->name }}</a></span> @endforeach
                                                </div>
                                        </div>
                                    @endif
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm">
    </div>
</div>
@endsection
