@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    <b>{{ $user }}</b> has changed the additional status for the order on {{ $address }}.
    <br>
    <br>
    Current Additional Status: {{ $newAdditionalStatus }} @if($newAdditionalStatusComment)- {{ $newAdditionalStatusComment }} @endif<br>
    Previous Additional Status: {{ $oldAdditionalStatus }} @if($oldAdditionalStatusComment)- {{ $oldAdditionalStatusComment }} @endif
    <br>
    <br>
    File#: {{ $fileNumber }}<br>
    Loan#: {{ $loanNumber }}<br>
    Borrower: {{ $borrower }}<br>
    <br>
    <br>
    Please follow this <a href="{{ $actionUrl }}">link</a> to see the order in more details.
@stop