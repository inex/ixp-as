@extends('layouts.master')

@section('content')

<h1>
    Request:
    <small>#<a href="{{ URL::to( '/result' ) . '/' . $nonce }}">{{ $nonce}}</a></small>
</h1>

<ul>
    <li> <strong>Requested:</strong> {{ $request->created->format('Y-m-d H:i:s') }} </li>
    <li> <strong>Started:</strong> {{ $request->started ? $request->started->format('Y-m-d H:i:s') : 'Pending' }} </li>
    @if ($request->started)
        <li> <strong>Completed:</strong> {{ $request->completed ? $request->completed->format('Y-m-d H:i:s') : 'Still running' }} </li>
    @endif
</ul>

<h2>
    Results for:
        {{ $request->snetwork->name }} (AS{{$request->snetwork->asn}})
        @ {{ $request->snetwork->ixp->shortname }}
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

<br><br><br><br>
@endsection
