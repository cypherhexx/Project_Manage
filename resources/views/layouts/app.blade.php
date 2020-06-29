<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ get_favicon() }}">
    <title>{{ config('app.name', 'MicroElephant CRM & Project Management System') }}</title>
    <link rel="stylesheet" href="{{ url(mix('/css/guest.css')) }}">
    @if(is_recaptcha_enable())
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback" async defer></script>
    @endif    
    <style type="text/css">
        .btn {
            cursor: pointer;
        }
    </style>
    <style type="text/css">
    .t-heading.-size-l, .-size-l.modal__heading, .swagger-ui h1.-size-l, .swagger-ui h2, .swagger-ui h3.-size-l, .swagger-ui h4.-size-l, .swagger-ui h5.-size-l, .swagger-ui h6.-size-l {
    color: #47aaa3;
    font-size: 1.7em;
    font-weight: 300;
    padding-top: 5px;
    padding-bottom: 10px;
    text-align: center;
}
.required{
    color: red;
}
.card-body{
    font-size: 13px;
}
</style>
</head>
<body style="background-color: #eee;">
    @include('layouts.customer.menu')
    @yield('content')


<script type="text/javascript" src="{{ url(mix('/js/guest.js')) }}"></script>

@if(is_recaptcha_enable())    
<script>
    var onloadCallback = function() {
        grecaptcha.execute();
    };

    function setResponse(response) { 
        document.getElementById('recaptcha').value = response; 
    }

    
</script>
@endif

<script type="text/javascript">
    $(function(){       
        $( ".selectpicker" ).select2( {
            theme: "bootstrap",
            placeholder: function(){
                $(this).data('placeholder');
            },
            maximumSelectionSize: 6
        } );
    });
     window.onload = function () { 
    
        $("#sign_in").prop('disabled', false);
    }
</script>
</body>
</html>
