@extends('user.layout')

@section('profile-title') {{ $user->name }}'s Award Case @endsection

@section('profile-content')
{!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Award Case' => $user->url . '/awardcase']) !!}

<h1>
    Awards
</h1>

@foreach($awards as $categoryId=>$categoryAwards)
    <div class="card mb-3 inventory-category">
        <h5 class="card-header inventory-header">
            {!! isset($categories[$categoryId]) ? '<a href="'.$categories[$categoryId]->searchUrl.'">'.$categories[$categoryId]->name.'</a>' : 'Miscellaneous' !!}
        </h5>
        <div class="card-body inventory-body">
            @foreach($categoryAwards->chunk(4) as $chunk)
                <div class="row mb-3">
                    @foreach($chunk as $awardId=>$stack)
                        <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $stack->first()->pivot->id }}" data-name="{{ $user->name }}'s {{ $stack->first()->name }}">
                            <div class="mb-1">
                                <a href="#" class="awardcase-stack"><img src="{{ $stack->first()->imageUrl }}" /></a>
                            </div>
                            <div>
                                <a href="#" class="awardcase-stack awardcase-stack-name">{{ $stack->first()->name }} x{{ $stack->sum('pivot.count') }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endforeach


<h3>Latest Activity</h3>
<div class="row ml-md-2 mb-4">
  <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
    <div class="col-6 col-md-2 font-weight-bold">Sender</div>
    <div class="col-6 col-md-2 font-weight-bold">Recipient</div>
    <div class="col-6 col-md-2 font-weight-bold">Award</div>
    <div class="col-6 col-md-4 font-weight-bold">Log</div>
    <div class="col-6 col-md-2 font-weight-bold">Date</div>
  </div>
      @foreach($logs as $log)
          @include('user._award_log_row', ['log' => $log, 'owner' => $user])
      @endforeach
</div>
<div class="text-right">
    <a href="{{ url($user->url.'/award-logs') }}">View all...</a>
</div>

@endsection

@section('scripts')
<script>

$( document ).ready(function() {
    $('.awardcase-stack').on('click', function(e) {
        e.preventDefault();
        var $parent = $(this).parent().parent();
        loadModal("{{ url('awards') }}/" + $parent.data('id'), $parent.data('name'));
    });
});

</script>
@endsection