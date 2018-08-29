@extends('emails.shared.layout')

@section('content')
Hello {{ $firstName }},

Your manager account has been created. You can now login using the username and password below. Please login by clicking <a href="{{ $loginUrl }}">here</a>.

Username: {{ $username }}
Password: {{ $password }}
@stop
