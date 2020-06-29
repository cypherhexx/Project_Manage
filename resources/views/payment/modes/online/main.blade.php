@extends('setup.index')
@section('title', __('form.settings') . " : ". __('form.online_payment_modes'))
@section('setting_page')
    @php
        $route_name 	= Route::currentRouteName();
        $group_name 	= app('request')->input('group');
        $sub_group_name = app('request')->input('subgroup');
        $main_url 		= route('payment_modes_online_page');
    @endphp


<div class="main-content" style="margin-bottom: 20px !important;">
   <div class="row">
      <div class="col-md-6">
         <h5>{{ __('form.online_payment_modes') }}</h5>
      </div>     
   </div>
   <ul class="nav project-navigation">
      @if(isset($data['tabs']) && count($data['tabs']) > 0)
         @foreach($data['tabs'] as $tabs)
            <li class="nav-item">
                <?php  
                    $active_tab = ($data['default_gateway_unique_identifier'] == $tabs['unique_identifier']) ?  '' : $tabs['unique_identifier']; 
                ?>
                <a class="nav-link {{ is_active_nav($active_tab, $group_name) }}" href="{{ $main_url }}?group={{ $tabs['unique_identifier'] }}">{{ $tabs['display_name'] }}</a>
            </li>

         @endforeach
      @endif
   </ul>
</div>

<?php

$gateway = (isset($rec->{$data['unique_identifier']})) ? $rec->{$data['unique_identifier']} : []; 
$old     = old('settings');

?>


<div class="main-content">
<form method="POST" action="{{ route('post_payment_modes_online') }}">
     {{ csrf_field()  }}
    <input type="hidden" name="payment_mode_id" value="{{ old_set('payment_mode_id', NULL, $gateway, $old) }}">
   @include($data['view_file'])
</div>
<?php echo bottom_toolbar(); ?>
</form>
</div>
@endsection

@section('onPageJs')

@yield('innerPageJS')
    

@endsection