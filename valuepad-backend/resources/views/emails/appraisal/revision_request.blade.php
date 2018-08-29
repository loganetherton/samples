@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    <b>{{ $user }}</b> has sent you a revision request for the order on {{ $address }}.
    <br>
    <br>
    File#: {{ $fileNumber }}<br>
    Loan#: {{ $loanNumber }}<br>
    Borrower: {{ $borrower }}<br>
    <br>
    <br>
    Please follow this <a href="{{ $actionUrl }}">link</a> to view the revision.
@stop