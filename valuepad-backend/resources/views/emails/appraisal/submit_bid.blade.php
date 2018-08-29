@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    <b>{{ $user }}</b> has submitted a bid for the order on {{ $address }}.
    <br>
    <br>
    <b>Amount:</b> {{ $bid['amount'] }}
    <br>
    <b>Estimated Completion Date:</b> {{ $bid['ecd'] }}
    <br>
    <b>Comments:</b> {{ $bid['comments'] }}
@stop