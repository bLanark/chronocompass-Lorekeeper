@extends('admin.layout')

@section('admin-title') Awards @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Awards' => 'admin/data/awards']) !!}

<h1>Awards</h1>

<p>This is a list of awards in the game. Awards cannot be transferred (unlike items) but can be deleted. Awards can be granted via prompts, claims, or admin grants. Note that by default, users can receive more than one award. (In the case that award systems vary between ARPGS) So be sure to check a user's awards before granting if you want specific awards to be exclusive.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/awards/create') }}"><i class="fas fa-plus"></i> Create New Award</a></div>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('award_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($awards))
    <p>No awards found.</p>
@else
    {!! $awards->render() !!}

    <table class="table table-sm category-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($awards as $award)
                <tr class="sort-award" data-id="{{ $award->id }}">
                    <td><a href="{{ $award->idUrl }}">{{ $award->name }}</a></td>
                    <td>{{ $award->category ? $award->category->name : '' }}</td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/awards/edit/'.$award->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>




    {!! $awards->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection
