@extends('emails.layout')
@section('email_content')


<h3 style="box-sizing:border-box;border:0;margin:0;padding:0;font-family:'Apple Garamond','Baskerville','Times New Roman','Droid Serif','Times','Source Serif Pro',serif;font-weight:bold;margin-bottom:20px">{{ $rec['name'] }},</h3>
<p style="box-sizing:border-box;border:0;padding:0;margin:0 0 18px">Please confirm your registration by clicking on the link below:</p>

<a href="{{ $rec['url'] }}" style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;border-radius:3px;color:#fff;display:inline-block;text-decoration:none;background-color:#3097d1;border-top:10px solid #3097d1;border-right:18px solid #3097d1;border-bottom:10px solid #3097d1;border-left:18px solid #3097d1; text-align: center;" target="_blank">Confirm Your Account</a>
<br><br>
<p style="box-sizing:border-box;border:0;padding:0;margin:0 0 18px">If you have not registered to join us please ignore this message.</p>
<p style="box-sizing:border-box;border:0;padding:0;margin:0 0 18px">{{ config('constants.email_signature') }}</p>

@endsection