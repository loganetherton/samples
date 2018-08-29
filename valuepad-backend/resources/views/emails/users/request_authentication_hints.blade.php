@extends('emails.shared.layout')

@section('content')
    @if (count($data) > 1)
        Hello,<br/><br/>
        We found multiple users associated with this email address. Below you will find a list of the users as well as a link to reset the password if needed. <br/><br/>
        <table>
            <tr>
                <th style="text-align: left; padding-right: 15px;">First Name</th>
                <th style="text-align: left; padding-right: 15px;">Last Name</th>
                <th style="text-align: left; padding-right: 15px;">Username</th>
                <th>&nbsp;</th>
            </tr>
            @foreach($data as $row)
                <tr>
                    @if(isset($row['firstName']) && isset($row['lastName']))
                        <td style="text-align: left; padding-right: 15px;">{{ $row['firstName'] }}</td>
                        <td style="text-align: left; padding-right: 15px;">{{ $row['lastName'] }}</td>
                    @else
                        <td colspan="2">{{ $row['name'] }}</td>
                    @endif
                    <td style="text-align: left; padding-right: 15px;">{{ $row['username'] }}</td>
                    <td style="text-align: left"><a href="{{ $row['actionUrl'] }}">Reset Password</a></td>
                </tr>

            @endforeach
        </table>
    @else
        @if (isset($data[0]['firstName']))
        Hello {{ $data[0]['firstName'] }},
        @else
        Hello,
        @endif
        <br/><br/>
        Your username is <b>{{ $data[0]['username'] }}</b>.
        <br><br/>
        If you would like to reset your password please follow this <a href="{{ $data[0]['actionUrl'] }}">link</a>.
    @endif
@stop