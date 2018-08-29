@extends('emails.shared.layout')

@section('content')
    Hello {{ $name }},<br>
    You have requested to reset the password from your account on ValuePad.
    <br><br>
    Please follow this <a href="{{ $actionUrl }}">link</a> to set a new password.
@stop