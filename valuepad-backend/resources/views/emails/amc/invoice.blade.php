@extends('emails.shared.layout')
@section('content')
    Hello,<br/>
    Your invoice for {{ $from }} - {{ $to }} is ready.
    <br>
    <br>
    Please follow this <a href="{{ $actionUrl }}">link</a> to view it.
@stop