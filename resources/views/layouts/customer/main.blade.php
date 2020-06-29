<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">   
    <link rel="stylesheet" href="{{  url(mix('css/vendor.css')) }}">
    <link rel="stylesheet" href="{{  url(mix('css/app.css')) }}">
    {{ load_extended_files('customer_css') }}
</head>
<body>
<div id="notificationList"></div>
@include('layouts.customer.menu')
<div class="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <br>
                @yield('content')
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    global_config = {
        csrf_token                          : "{{ csrf_token() }}",
        url_get_unread_notifications        : "", 
        lang_no_record_found                : "",
        url_global_search                   : "",

        url_upload_attachment               : "{{ route('cp_upload_attachment') }}",
        url_delete_temporary_attachment     : "{{ route('cp_delete_temporary_attachment')}}",
        txt_delete_confirm_title            : "{{ __('form.delete_confirm_title') }}",
        txt_delete_confirm_text             : "{{ __('form.delete_confirm_text') }}",
        txt_btn_cancel                      : "{{ __('form.btn_cancel') }}",
        txt_yes                             : "{{ __('form.yes') }}",        
        is_pusher_enable                    : false

    };
</script>

<script type="text/javascript" src="{{  url(mix('js/app.js')) }}"></script>
<script  type="text/javascript" src="{{  url(mix('js/vendor.js')) }}"></script>
<script  type="text/javascript" src="{{  url(mix('js/main.js')) }}"></script>
<script  type="text/javascript" src="{{ asset('vendor/gantt-chart/js/modified_jquery.fn.gantt.min.js') }}"></script>
{{ load_extended_files('customer_js') }}
<script type="text/javascript">
  accounting.settings = <?php echo json_encode(config('constants.money_format')) ?>;

    $(function(){

         <?php if($flash = session('message')) {?>
            $.jGrowl("<?php echo $flash; ?>", { position: 'bottom-right'});
        <?php } ?>
    });

</script>

<script  src="{{  url(mix('js/tinymce.js')) }}"></script>
@yield('onPageJs')

</body>

</html>
