@extends('admin.layout')

@section('admin-title') Awards @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Awards' => 'admin/data/awards', ($award->id ? 'Edit' : 'Create').' Award' => $award->id ? 'admin/data/awards/edit/'.$award->id : 'admin/data/awards/create']) !!}

<h1>{{ $award->id ? 'Edit' : 'Create' }} Award
    @if($award->id)
        <a href="#" class="btn btn-outline-danger float-right delete-award-button">Delete Award</a>
    @endif
</h1>

{!! Form::open(['url' => $award->id ? 'admin/data/awards/edit/'.$award->id : 'admin/data/awards/create', 'files' => true]) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('Name') !!}
    {!! Form::text('name', $award->name, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
    <div>{!! Form::file('image') !!}</div>
    <div class="text-muted">Recommended size: 100px x 100px</div>
    @if($award->has_image)
        <div class="form-check">
            {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
            {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
        </div>
    @endif
</div>

<div class="row">
    <div class="col">
        <div class="form-group">
            {!! Form::label('Award Category (Optional)') !!}
            {!! Form::select('award_category_id', $categories, $award->award_category_id, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col">
        <div class="form-group">
            {!! Form::label('Award Rarity (Optional)') !!} {!! add_help('This should be a number.') !!}
            {!! Form::text('rarity', $award && $award->rarity ? $award->rarity : '', ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="form-group">
            {!! Form::label('Reference Link (Optional)') !!} {!! add_help('An optional link to an additional reference') !!}
            {!! Form::text('reference_url', $award->reference_url, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col">
    {!! Form::label('Award Artist (Optional)') !!} {!! add_help('Provide the artist\'s dA alias or, failing that, a link. If both are provided, the alias will be used as the display name for the link.') !!}
        <div class="row">
            <div class="col">
                <div class="form-group">
                    {!! Form::text('artist_alias', $award && $award->artist_alias ? $award->artist_alias : '', ['class' => 'form-control mr-2', 'placeholder' => 'Artist Alias']) !!}
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    {!! Form::text('artist_url', $award && $award->artist_url ? $award->artist_url : '', ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('Description (Optional)') !!}
    {!! Form::textarea('description', $award->description, ['class' => 'form-control wysiwyg']) !!}
</div>

<div class="form-group">
    {!! Form::label('release', 'Source (Optional)') !!} {!! add_help('Where or How this award was earned.') !!}
    {!! Form::text('release', $award && $award->source ? $award->source : '', ['class' => 'form-control']) !!}
</div>

<h3>Availability Information</h3>

<div class="row">
    <div class="col">
        <div class="form-group">
            {!! Form::label('prompts[]', 'Drop Location(s) (Optional)') !!} {!! add_help('You can select up to 10 prompts at once.') !!}
            {!! Form::select('prompts[]', $prompts, $award && isset($award->data['prompts']) ? $award->data['prompts'] : '', ['id' => 'promptsList', 'class' => 'form-control', 'multiple']) !!}
        </div>
    </div>
</div>

<div class="text-right">
    {!! Form::submit($award->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}


@if($award->id)
    <h3>Preview</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._award_entry', ['imageUrl' => $award->imageUrl, 'name' => $award->displayName, 'description' => $award->parsed_description, 'searchUrl' => $award->searchUrl])
        </div>
    </div>
   
@endif

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {    
    $('#shopsList').selectize({
            maxAwards: 10
        });

    $('#promptsList').selectize({
        maxAwards: 10
    });
    
    $('.delete-award-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/awards/delete') }}/{{ $award->id }}", 'Delete Award');
    });
});
    
</script>
@endsection