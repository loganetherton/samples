@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    <b>{{ $user }}</b> has accepted the order on {{ $address }} with the following conditions:
    <br>
    <br>

    <b>Reason:</b> {{ $conditions['reason'] }}
    <br>
    <b>Explanation:</b> {{ $conditions['explanation'] }}
    <br>
    <b>Total Requested Fee:</b> {{ $conditions['fee'] }}
    <br>
    <b>Due Date:</b> {{ $conditions['dueDate'] }}
@stop