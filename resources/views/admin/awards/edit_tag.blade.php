@extends('admin.layout')

@section('admin-title') Edit Award Tag @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Awards' => 'admin/data/awards', 'Edit Award' => 'admin/data/awards/edit/'.$award->id, 'Edit Tag Settings - ' . $tag->tag => 'admin/data/awards/tag/'.$award->id.'/'.$tag->tag]) !!}

<h1>
    Edit Tag Settings - {!! $tag->displayTag !!}
    <a href="#" class="btn btn-outline-danger float-right delete-tag-button">Delete Tag</a>
</h1>

<p>Edit the parameters for this award tag on this award. Note that for the award tag to take effect (e.g. become a usable award), you will need to turn on the Active toggle. (Conversely, you can turn it off to prevent users from using it while preserving the old settings for future use.)</p> 

@if(View::exists('admin.awards.tags.'.$tag->tag.'_pre'))
    @include('admin.awards.tags.'.$tag->tag.'_pre', ['award' => $award, 'tag' => $tag])
@endif
{!! Form::open(['url' => 'admin/data/awards/tag/'.$award->id.'/'.$tag->tag]) !!}

    @if(View::exists('admin.awards.tags.'.$tag->tag))
        @include('admin.awards.tags.'.$tag->tag, ['award' => $award, 'tag' => $tag])
    @endif

    {!! Form::checkbox('is_active', 1, $tag->is_active, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Active', ['class' => 'form-check-label ml-3']) !!}

    <div class="text-right">
        {!! Form::submit('Edit Tag Settings', ['class' => 'btn btn-primary']) !!}
    </div>

{!! Form::close() !!}
@if(View::exists('admin.awards.tags.'.$tag->tag.'_post'))
    @include('admin.awards.tags.'.$tag->tag.'_post', ['award' => $award, 'tag' => $tag])
@endif

@endsection

@section('scripts')
@parent
@if(View::exists('js.admin_awards.'.$tag->tag))
    @include('js.admin_awards.'.$tag->tag, ['award' => $award, 'tag' => $tag])
@endif
<script>
$( document ).ready(function() {    
    $('.delete-tag-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/data/awards/delete-tag') }}/{{ $award->id }}/{{ $tag->tag }}", 'Delete Tag');
    });
});
    
</script>
@endsection