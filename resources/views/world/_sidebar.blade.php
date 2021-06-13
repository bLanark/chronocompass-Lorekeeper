<ul>
    <li class="sidebar-header"><a href="{{ url('world') }}" class="card-link">Encyclopedia</a></li>
    @if(isset($sections) && $sections->count() > 0)
    <li class="sidebar-section">
        <div class="sidebar-section-header">Info</div>
        @foreach($sections as $section)
            <div class="sidebar-item"><a href="{{ url($section->url) }}" class="{{ set_active('world/info/'.$section->key) }}">{{ $section->name }}</a></div>
        @endforeach
    </li>
    @endif
    <li class="sidebar-section">
        <div class="sidebar-item"><a href="{{ url('world/info') }}">World Expanded</a></div>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Characters</div>
        <div class="sidebar-item"><a href="{{ url('world/species') }}" class="{{ set_active('world/species*') }}">Species</a></div>
        <div class="sidebar-item"><a href="{{ url('world/subtypes') }}" class="{{ set_active('world/subtypes*') }}">Subtypes</a></div>
        <div class="sidebar-item"><a href="{{ url('world/rarities') }}" class="{{ set_active('world/rarities*') }}">Rarities</a></div>
        <div class="sidebar-item"><a href="{{ url('world/trait-categories') }}" class="{{ set_active('world/trait-categories*') }}">Trait Categories</a></div>
        <div class="sidebar-item"><a href="{{ url('world/traits') }}" class="{{ set_active('world/traits*') }}">All Traits</a></div>
        <div class="sidebar-item"><a href="{{ url('world/character-categories') }}" class="{{ set_active('world/character-categories*') }}">Character Categories</a></div>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Items</div>
        <div class="sidebar-item"><a href="{{ url('world/item-categories') }}" class="{{ set_active('world/item-categories*') }}">Item Categories</a></div>
        <div class="sidebar-item"><a href="{{ url('world/items') }}" class="{{ set_active('world/items*') }}">All Items</a></div>
        <div class="sidebar-item"><a href="{{ url('world/currencies') }}" class="{{ set_active('world/currencies*') }}">Currencies</a></div>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Awards</div>
        <div class="sidebar-item"><a href="{{ url('world/award-categories') }}" class="{{ set_active('world/award-categories*') }}">Award Categories</a></div>
        <div class="sidebar-item"><a href="{{ url('world/awards') }}" class="{{ set_active('world/awards*') }}">All Awards</a></div>
        <div class="sidebar-section-header">Recipes</div>
        <div class="sidebar-item"><a href="{{ url('world/recipes') }}" class="{{ set_active('world/recipes*') }}">All Recipes</a></div>
    </li>
</ul>