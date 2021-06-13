@extends('admin.layout')

@section('admin-title') Grant Awards @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Awards' => 'admin/grants/awards']) !!}

<h1>Grant Awards</h1>

{!! Form::open(['url' => 'admin/grants/awards']) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('names[]', 'Username(s)') !!} {!! add_help('You can select up to 10 users at once.') !!}
    {!! Form::select('names[]', $users, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
</div>

<div class="form-group">
    {!! Form::label('Award(s)') !!} {!! add_help('Must have at least 1 award and Quantity must be at least 1.') !!}
    <div id="awardList">
        <div class="d-flex mb-2">
            {!! Form::select('award_ids[]', $awards , null, ['class' => 'form-control mr-2 default award-select', 'placeholder' => 'Select Award']) !!}
            {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
            <a href="#" class="remove-award btn btn-danger mb-2 disabled">×</a>
        </div>
    </div>
    <div><a href="#" class="btn btn-primary" id="add-award">Add Award</a></div>
    <div class="award-row hide mb-2">
        {!! Form::select('award_ids[]', $awards, null, ['class' => 'form-control mr-2 award-select', 'placeholder' => 'Select Award']) !!}
        {!! Form::text('quantities[]', 1, ['class' => 'form-control mr-2', 'placeholder' => 'Quantity']) !!}
        <a href="#" class="remove-award btn btn-danger mb-2">×</a>
    </div>
</div>

<div class="form-group">
    {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs and in the inventory description.') !!}
    {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>

<h3>Additional Data</h3>

<div class="form-group">
    {!! Form::label('notes', 'Notes (Optional)') !!} {!! add_help('Additional notes for the award. This will appear in the award\'s description, but not in the logs.') !!}
    {!! Form::text('notes', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>



<div class="text-right">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('#usernameList').selectize({
            maxAwards: 10
        });
        $('.default.award-select').selectize();
        $('#add-award').on('click', function(e) {
            e.preventDefault();
            addAwardRow();
        });
        $('.remove-award').on('click', function(e) {
            e.preventDefault();
            removeAwardRow($(this));
        })
        function addAwardRow() {
            var $rows = $("#awardList > div")
            if($rows.length === 1) {
                $rows.find('.remove-award').removeClass('disabled')
            }
            var $clone = $('.award-row').clone();
            $('#awardList').append($clone);
            $clone.removeClass('hide award-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-award').on('click', function(e) {
                e.preventDefault();
                removeAwardRow($(this));
            })
            $clone.find('.award-select').selectize();
        }
        function removeAwardRow($trigger) {
            $trigger.parent().remove();
            var $rows = $("#awardList > div")
            if($rows.length === 1) {
                $rows.find('.remove-award').addClass('disabled')
            }
        }
    });

</script>

@endsection