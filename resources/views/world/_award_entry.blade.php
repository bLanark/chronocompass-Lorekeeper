<div class="row world-entry">
    @if($imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" /></a></div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <h3>{!! $name !!} @if(isset($idUrl) && $idUrl) <a href="{{ $idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>  @endif</h3>
        <div class="row">
            @if(isset($award->category) && $award->category)
                <div class="col">
                    <p><strong>Category:</strong> {!! $award->category->name !!}</p>
                </div>
            @endif
            @if(isset($award->rarity) && $award->rarity)
                <div class="col">
                    <p><strong>Rarity:</strong> {!! $award->rarity !!}</p>
                </div>
            @endif
            @if(isset($award->artist) && $award->artist)
                <div class="col">
                    <p><strong>Artist:</strong> {!! $award->artist !!}</p>
                </div>
            @endif
            <div class="col-md-6 col-md">
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
            @if(isset($award->reference) && $award->reference)  <p><strong>Reference Link:</strong> <a href="{{ $award->reference }}">{{ $award->reference }}</a></p> @endif
            {!! $description !!}
            @if(isset($award->uses) && $award->uses || isset($award->source) && $award->source || isset($award->data['shops']) && $award->data['shops'] || isset($award->data['prompts']) && $award->data['prompts'])
            <div class="text-right"><a data-toggle="collapse" href="#award-{{ $award->id }}" class="text-primary"><strong>Show details...</strong></a></div>
            <div class="collapse" id="award-{{ $award->id }}">
                @if(isset($award->uses) && $award->uses)  <p><strong>Uses:</strong> {{ $award->uses }}</p> @endif
                @if(isset($award->source) && $award->source || isset($award->data['shops']) && $award->data['shops'] || isset($award->data['prompts']) && $award->data['prompts'])
                <h5>Availability</h5>
                <div class="row">
                    @if(isset($award->source) && $award->source)
                        <div class="col">
                            <p><strong>Source:</strong></p> 
                            <p>{!! $award->source !!}</p>
                        </div>
                    @endif
                    @if(isset($award->data['shops']) && $award->data['shops'])
                        <div class="col">
                            <p><strong>Purchaseable At:</strong></p>
                                <div class="row">
                                    @foreach($award->shops as $shop) <div class="col"><a href="{{ $shop->url }}">{{ $shop->name }}</a></div> @endforeach
                                </div>
                        </div>
                    @endif
                    @if(isset($award->data['prompts']) && $award->data['prompts'])
                        <div class="col">
                            <p><strong>Drops From:</strong></p>
                                <div class="row">
                                    @foreach($award->prompts as $prompt) <div class="col"><a href="{{ $prompt->url }}">{{ $prompt->name }}</a></div> @endforeach
                                </div>
                        </div>
                    @endif
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>