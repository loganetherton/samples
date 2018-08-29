@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    <b>{{ $user }}</b> has deleted the {{ $document }} additional document from the order on {{ $address }}.
    <br>
    <br>
    File#: {{ $fileNumber }}<br>
    Loan#: {{ $loanNumber }}<br>
    Borrower: {{ $borrower }}
@stop