@extends('layouts.master')

@section('content')

<h1>Result for Request #<a href="{{ URL::to( '/result' ) . $nonce }}">{{ $nonce}}</a></h1>

<h2>
    Results for:
        {{ $request->snetwork->name }} (AS{{$request->snetwork->asn}})
        @ {{ $request->snetwork->ixp->shortname }}
        (IPv{{ $request->protocol }})
</h2>

<ul>

@foreach ( $request->measurements as $m )

    <li>
        {{ $m->dnetwork->name}} (AS{{ $m->dnetwork->asn}})

        @if ( !$m->result )
            <span class="label label-default">PENDING</span>

        @else

            @if ( $m->result->routing == 'IXP_SYM' )
                <span class="label label-success">SYMMETRIC</span>
            @elseif ( $m->result->routing == 'IXP_ASYM_OUT' )
                <span class="label label-danger">ASYMMETRIC OUT</span>
            @elseif ( $m->result->routing == 'IXP_ASYM_IN' )
                <span class="label label-danger">ASYMMETRIC IN</span>
            @elseif ( $m->result->routing == 'NON_IXP' )
                <span class="label label-info">NON IXP</span>
            @else
                <span class="label label-primary">WTF?</span>
            @endif

            

        @endif





@endforeach

</ul>

<br><br><br><br>
@endsection
