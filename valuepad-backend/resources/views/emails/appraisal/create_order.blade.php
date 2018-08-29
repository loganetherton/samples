@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    <b>{{ $user }}</b> has assigned a new order to you on {{ $address }}.
    <br>
    <br>
    File#: {{ $fileNumber }}<br>
    Loan#: {{ $loanNumber }}<br>
    Borrower: {{ $borrower }}<br>
    Instruction: {!! $instruction !!}
    <br>
    <br>
    Please follow this <a href="{{ $actionUrl }}">link</a> to decline/accept the order.
@stop