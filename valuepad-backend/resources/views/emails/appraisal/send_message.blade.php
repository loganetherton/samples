@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    <b>{{ $user }}</b> has sent you a message for the order on {{ $address }}.
    <br>
    <br>
    Message: {{ $content }}
    <br>
    <br>
    File#: {{ $fileNumber }}<br>
    Loan#: {{ $loanNumber }}<br>
    Borrower: {{ $borrower }}<br>
    <br>
    <br>
    Please follow this <a href="{{ $actionUrl }}">link</a> to see the message in more details.
@stop