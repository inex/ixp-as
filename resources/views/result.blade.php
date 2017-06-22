@extends('layouts.master')

@section('content')

@if (session('duplicate'))
    <div class="alert alert-danger">
        A new request was not created as there is an open request for the same IXP / network / protocol
        and the current results for this are below.
    </div>
@endif

<h1>
    Request:
    <small>#<a href="{{ URL::to( '/result' ) . '/' . $nonce }}">{{ $nonce}}</a></small>
</h1>

<ul>
    <li> <strong>Requested:</strong> {{ $request->created->format('Y-m-d H:i:s') }} </li>
    <li> <strong>Started:</strong> {{ $request->started ? $request->started->format('Y-m-d H:i:s') : 'Pending' }} </li>
    @if ($request->started)
        <li> <strong>Completed:</strong>
            @if ($request->completed)
                {{ $request->completed->format('Y-m-d H:i:s') }}
            @else
                Still running. (page reloads in: <span id="seconds">60</span> seconds)

                <script>
                (function countdown(remaining) {
                    if(remaining <= 0)
                        location.reload(true);
                    document.getElementById('seconds').innerHTML = remaining;
                    setTimeout(function(){ countdown(remaining - 1); }, 1000);
                })(60); // 60 seconds
                </script>

            @endif
        </li>
    @endif
</ul>

<h2>
    Results for:
        {{ $request->snetwork->name }} (AS{{$request->snetwork->asn}})
        @ {{ $request->ixp->shortname }}
        (IPv{{ $request->protocol }})
</h2>

@if (count( $request->measurements) )

    @include('results.measurements')

@elseif (!$request->started)

    @include('results.notstarted')

@elseif ($request->started && !$request->completed)

    @include('results.started')

@elseif ($request->started && $request->completed)

    @include('results.none')

@endif

<br><br>

<h3>Networks Without Probes at {{ $request->ixp->shortname }}</h3>

@if( count( $networksWithoutProbes ) )

    <p>
        The following networks do not have RIPE Atlas probes at {{ $request->ixp->shortname }} for IPv{{ $request->protocol }}.
    </p>

    <ul>
        @foreach( $networksWithoutProbes as $n )
            <li> AS{{ $n->getAsn() }} - {{ $n->getName() }}</li>
        @endforeach
    </ul>

@else

    <p>Woohoo! All networks have RIPE Atlas probes!</p>

@endif


<div id="modal-json" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modal-json-header" ></h4>
        </div>
        <div class="modal-body" id="modal-json-body">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
  </div>
</div>


@endsection

@section('scripts')
<script>
    var results = {!! $json !!};
    var whois  = "{!! URL::to('/whois') !!}";
</script>

<script src="{{ URL::asset('js/result.js' ) }}"></script>
@endsection
