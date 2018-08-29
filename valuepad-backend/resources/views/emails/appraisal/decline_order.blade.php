@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    <b>{{ $user }}</b> has declined the order on {{ $address }} for the following reason:
    <br>
    <br>

    <b>Reason:</b> {{ $reason }}
    <br>
    <b>Explanation:</b> {{ $explanation }}
@stop