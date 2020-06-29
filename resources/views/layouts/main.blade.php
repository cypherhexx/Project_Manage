<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="shortcut icon" href="{{ get_favicon() }}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">   
    <link rel="stylesheet" href="{{  url(mix('css/vendor.css')) }}">
    <link rel="stylesheet" href="{{  url(mix('css/app.css')) }}">    
    <style>
.four-boot .dropdown .btn{
    
    overflow: hidden !important;
    white-space: nowrap !important;
    display: block !important;
    text-overflow: ellipsis !important;
}
.toolbar {
    float:left;
}
 .list-group-item  .remove_tmp_attachment{
          float: right !important;
          text-align: right;

        }

        iframe  .panel-heading .panel-title{
          display: none !important;
        }

  
  /* relevant styles */
.img__wrap {
  position: relative;
 
}

.img__description_layer {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(36, 62, 206, 0.6);
  color: #fff;
  visibility: hidden;
  opacity: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 30px;

  /* transition effect. not necessary */
  transition: opacity .2s, visibility .2s;
}

.img__wrap:hover .img__description_layer {
  visibility: visible;
  opacity: 1;
}

.img__description {
  transition: .2s;
  transform: translateY(1em);
}

.img__wrap:hover .img__description {
  transform: translateY(0);
}

.daterangepicker{
    z-index: 1100 !important;
}
.btn{
  border-radius: 2px;

}
</style>
{{ load_extended_files('admin_css') }}

</head>
<body>

<nav class="navbar navbar-expand navbar-dark bg-dark flex-md-nowrap">
   <a class="navbar-brand navbar-brand col-sm-3 col-md-2 mr-0 d-none d-sm-block" href="{{ route('dashboard') }}">
      <div>
        @if(get_company_logo(NULL, TRUE))
          <img src="{{ get_company_logo(NULL, TRUE) }}" class="img-fluid" alt="{{ config('constants.company_name') }}">  
         @else 
          {{ get_company_logo(NULL, TRUE) }}
         @endif
      </div>
   </a>
   <div class="collapse navbar-collapse" id="navbarsExample02">
      <ul class="navbar-nav">
         <li class="nav-item active">
            <a href="#" id="sidebarCollapse"><i class="fa fa-bars"></i></a>
         </li>
      </ul>
      <form class="form-inline my-2 my-md-0  d-none d-md-block" style="margin-left: 20px; " autocomplete="off">
         <div class="input-group">
            <input type="text" id="global_search" class="form-control form-control-sm"  style="width: 400px;" placeholder="@lang('form.search_hash')" >
            <div class="input-group-append">
               <button class="btn btn-sm btn-outline-secondary" type="button" id="button-addon2"><i class="fas fa-search"></i></button>
            </div>
         </div>
      </form>
      <ul class="navbar-nav ml-auto">
         @include('notification_bell') 
         <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <img class="mr-2 staff-profile-image-small" src="{{ get_avatar_small_thumbnail(auth()->user()->photo) }}"> {{ auth()->user()->first_name }}</a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown01" style="min-width: 300px; font-size: 14px;">
               @if(auth()->user()->is_administrator)
               <h6 class="dropdown-header text-center">@lang('form.create_new')</h6>
               <div class="row" style="margin-left: 0px; margin-right: 0px;">
                  <div class="col-md-6">
                     <a href="{{ route('add_invoice_page') }}" class="dropdown-item">@lang('form.invoice')</a>
                     <a href="{{ route('add_estimate_page') }}" class="dropdown-item">@lang('form.estimate')</a>
                     <a href="{{ route('add_proposal_page') }}" class="dropdown-item">@lang('form.proposal')</a>
                     <a href="{{ route('add_expense_page') }}" class="dropdown-item">@lang('form.expense')</a>
                     <a href="{{ route('add_vendor_page') }}" class="dropdown-item">@lang('form.vendor')</a>
                  </div>
                  <div class="col-md-6">
                     <a href="{{ route('add_task_page') }}" class="dropdown-item">@lang('form.task')</a>
                     <a href="{{ route('add_ticket_page') }}" class="dropdown-item">@lang('form.ticket')</a>
                     <a href="{{ route('add_projects') }}" class="dropdown-item">@lang('form.project')</a>
                     <a href="{{ route('add_customer_page') }}" class="dropdown-item">@lang('form.customer')</a>
                     <a href="{{ route('add_lead_page') }}" class="dropdown-item">@lang('form.lead')</a>
                  </div>
               </div>
               <hr>
               @endif
               <a class="dropdown-item" href="{{ route('member_profile', auth()->user()->id) }}">@lang('form.my_account')</a>
               <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> @lang('form.logout')</a>
               <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                  {{ csrf_field() }}
               </form>
            </div>
         </li>
      </ul>
   </div>
</nav>
    
<div class="wrapper">
   <!-- Sidebar Holder -->
   @include('layouts.menu')
   <!-- Page Content Holder -->
   <div id="content">
    <br>
      <div class="container">
         <div class="row">
            <div class="col-md-12">               
               @include('layouts.flash_message')
               @yield('content')
            </div>
         </div>
      </div>
   </div>
</div>
<script type="text/javascript">

 <?php $pusher = get_pusher_api_info(); ?>

    global_config = {
        csrf_token                      : "{{ csrf_token() }}",
        url_get_unread_notifications    : "{{ route('get_unread_notifications') }}", 
        lang_no_record_found            : "{{ __('form.no_record_found') }}",
        url_global_search               : "{{ route('global_search') }}",
        url_upload_attachment           : "{{ route('upload_attachment') }}",
        url_delete_temporary_attachment : "{{ route('delete_temporary_attachment')}}",
        txt_delete_confirm_title        : "{{ __('form.delete_confirm_title') }}",
        txt_delete_confirm_text         : "{{ __('form.delete_confirm_text') }}",
        txt_btn_cancel                  : "{{ __('form.btn_cancel') }}",
        txt_yes                         : "{{ __('form.yes') }}",        
        is_pusher_enable                : {{ (is_pusher_enable()) ?  'true' : 'false' }},
        url_patch_note                  : "{{ route('patch_note') }}",
        url_delete_note                 : "{{ route('delete_note') }}"

    };

    <?php if(is_pusher_enable()) {?>

        global_config.pusher_log_status = {{ ( App::environment('local') || App::environment('development') ) ? 'true' : 'false' }};
        global_config.pusher_app_key    = '{{ $pusher->app_key }}';
        global_config.pusher_cluster    = "{{ ($pusher->app_cluster) ? $pusher->app_cluster : 'mt1' }}";
        global_config.pusher_channel    = 'chanel_{{ auth()->user()->id }}';

    <?php } ?>    

</script>
  
<script type="text/javascript" src="{{  url(mix('js/app.js')) }}"></script>
<script  type="text/javascript" src="{{  url(mix('js/vendor.js')) }}"></script>
<script  type="text/javascript" src="{{  url(mix('js/main.js')) }}"></script>
<script  type="text/javascript" src="{{ asset('vendor/gantt-chart/js/modified_jquery.fn.gantt.min.js') }}"></script>
{{ load_extended_files('admin_js') }}
<script type="text/javascript">  

  accounting.settings = <?php echo json_encode(config('constants.money_format')) ?>;

    $(function(){

         <?php if($flash = session('message')) {?>
            $.jGrowl("<?php echo $flash; ?>", { position: 'bottom-right'});
        <?php } ?>

        $('.currency_changed').change(function(){
            $(this)
        });
    });
  


$(document).on('click','.change_task_status',function(e){

        e.preventDefault();

        var url       = $(this).attr("href");
        var name      = $(this).data('name');
        var id        = $(this).data('id');
        var task_id   = $(this).data('task');


        if(url)
        {
          $scope = this;
          $.post(url , { "_token": global_config.csrf_token, task_id : task_id, status_id : id }).done(function( response ) {
                      
              if(response.status == 1)
              {
                $($scope).closest(".dropdown").find(".btn").text(name);
              }
              
          });

        }       

    });

$(document).ready(function() {
  $(document).on('focus', ':input', function() {
    $(this).attr('autocomplete', 'off');
  });
});

</script>


<!-- @if(is_pusher_enable())
    <script src="https://js.pusher.com/4.3/pusher.min.js"></script> 
@endif  -->
<script  src="{{  url(mix('js/tinymce.js')) }}"></script>

@yield('onPageJs')
 

</body>

</html>
