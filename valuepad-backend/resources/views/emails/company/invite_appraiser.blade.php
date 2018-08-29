@extends('emails.shared.layout')

@section('content')
Hello {{ $firstName }},

<p>
    You've been invited to join {{ $companyName }} on ValuePad. To accept the invitation, click <a href="{{ $loginUrl }}">here</a> to log into ValuePad and accept the invitation on the Invitations page.
</p>

<p>
    If you don't have an account, click <a href="{{ $signUpUrl }}">here</a> to create one.
</p>
@stop
