@extends('layouts.master')

@section('content')

<h1>Result for [js request nonce]</h1>

{{-- the below syntax outputs UNESCAPED strings --}}

JSON here: {!! $request !!}



@endsection
