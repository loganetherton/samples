@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    The following appraisal has not been accepted within {{ $hours }} hour(s) of being assigned.
    <br>
    <br>
    File#: {{ $fileNumber }}<br>
    Loan#: {{ $loanNumber }}<br>
    Borrower: {{ $borrower }}<br>
    <br>
    <br>
    Please follow this <a href="{{ $actionUrl }}">link</a> to accept/decline the appraisal.
@stop