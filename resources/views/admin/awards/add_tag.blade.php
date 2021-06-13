@extends('admin.layout')

@section('admin-title') Add Award Tag @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Awards' => 'admin/data/awards', 'Edit Award' => 'admin/data/awards/edit/'.$award->id, 'Add Award Tag' => 'admin/data/awards/tag/'.$award->id]) !!}

<h1>Add Award Tag</h1>

<p>Select an award tag to add to the award. You cannot add duplicate tags to the same award (they are removed from the selection). You will be taken to the parameter editing page after adding the tag. </p> 

{!! Form::open(['url' => 'admin/data/awards/tag/'.$award->id]) !!}

<div class="form-group">
    {!! Form::label('tag', 'Tag') !!}
    {!! Form::select('tag', [0 => 'Select a Tag'] + $tags, null, ['class' => 'form-control']) !!}
</div>

<div class="text-right">
    {!! Form::submit('Add Tag', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@endsection