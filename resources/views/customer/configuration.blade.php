@extends('setup.index')
@section('title', __('form.settings') . " : " . __('form.support') . " " .__('form.configuration') )
@section('setting_page')
<div class="main-content">
   
   <h5>{{ __('form.customer') . " " .__('form.configuration')  }}</h5>
   <hr>
   <div class="row">
      <div class="col-md-8">
         <form role="form" class="form-horizontal" enctype="multipart/form-data" action="{{ route('customer_support_configuration') }}" method="post" autocomplete="off" >
            {{ csrf_field()  }}
            {{ method_field('PATCH') }}

            <div class="custom-control custom-checkbox">
              <input {{ (old_set('disable_customer_registration', NULL, $rec)) ? 'checked' : '' }} type="checkbox" class="custom-control-input" 
              name="disable_customer_registration" value="1" id="disable_customer_registration">
              <label class="custom-control-label" for="disable_customer_registration">@lang('form.disable_customer_registration')</label>
            </div>


      
            <?php echo bottom_toolbar(); ?>
         </form>
      </div>
      <div class="col-md-4">
         
      </div>
   </div>
</div>
@endsection

@section('onPageJs')
<script>
   $(function() {
   
    
   
   });
   
   
</script>
@endsection