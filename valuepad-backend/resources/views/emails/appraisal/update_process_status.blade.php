@extends('emails.shared.layout')

@section('content')
    {{ $greeting }}
    <br>
    <b>{{ $user }}</b> has changed the process status for the order on {{ $address }}.
    <br>
    <br>
    Current Process Status: {{ $newProcessStatus }}<br>
    Previous Process Status: {{ $oldProcessStatus }}

    @if(isset($explanation))
        <br>Explanation: {{ $explanation }}
    @endif

    @if(isset($scheduledAt))
        <br>Scheduled Date: {{ $scheduledAt }}
    @endif

    @if(isset($completedAt))
        <br>Completed Date: {{ $completedAt }}
    @endif

    @if(isset($estimatedCompletionDate))
        <br>Estimated Completion Date: {{ $estimatedCompletionDate }}
    @endif

    <br>
    <br>
    File#: {{ $fileNumber }}<br>
    Loan#: {{ $loanNumber }}<br>
    Borrower: {{ $borrower }}<br>
    <br>
    <br>
    Please follow this <a href="{{ $actionUrl }}">link</a> to see the order in more details.
@stop