@extends('emails.shared.layout')
@section('content')
    <b>{{ $firstName }} {{ $lastName }}</b> has sent you documents related to Appraisal Order - {{ $fileNumber }}.
    <br>
    <br>
    <a href="{{ $document['url'] }}">{{ $document['name'] }}</a>
@stop