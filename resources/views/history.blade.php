@extends('layouts.master')

@section('content')

<h1>Request History</h1>

<ul>
    @foreach ($requests as $r)

    <li>
        <a href="{{ URL::to( '/result' ) . '/' . $r->getNonce() }}">
            {{ $r->getNetwork()->getName() }} (AS{{$r->getNetwork()->getAsn()}})
            @ {{ $r->getIXP()->getShortname() }}
            (IPv{{ $r->getProtocol() }})
        </a>

        @if ($r->getCompleted())
            <span class="label label-primary">COMPLETE</span>
        @elseif ($r->getStarted())
            <span class="label label-success">RUNNING</span>
        @else
            <span class="label label-default">PENDING</span>
        @endif

    </li>

    @endforeach
</ul>




<br><br><br><br>
@endsection
