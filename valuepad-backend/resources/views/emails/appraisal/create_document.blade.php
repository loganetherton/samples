@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    <b>{{ $user }}</b> has uploaded the {{ $document }} document for the order on {{ $address }}.
    <br>
    <br>
    File#: {{ $fileNumber }}<br>
    Loan#: {{ $loanNumber }}<br>
    Borrower: {{ $borrower }}
    <br>
    <br>
    Please follow this <a href="{{ $actionUrl }}">link</a> to download the document.
@stop