@extends('layouts.email')

@section('email-css')

@endsection

@section('content')
    <p>Hi there,</p>
    <p>
        <strong>Please confirm your email address</strong>, to activate your account. If you received this by mistake or weren't
        expecting it, please disregard this email.
    </p>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
        <tbody>
        <tr>
            <td align="left">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                    <tr>
                        <td>
                            <a href="http://3.15.4.53/#/activation?link_code={{$link_code}}">Confirm E-mail Address</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
    <p>Good luck! Hope it works.</p>

@endsection
