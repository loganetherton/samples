@extends('emails.shared.layout')
@section('content')
    Hello,<br/>
    Your AMC account has been approved.
    <br>
    <br>
    Please follow this <a href="{{ $actionUrl }}">link</a> to login.
@stop