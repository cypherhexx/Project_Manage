@extends('setup.index')
@section('title', __('form.settings') . " : " .__('form.invoice'))
@section('setting_page')
<div class="main-content">
   <h5>@lang('form.invoice')</h5>
   <hr>
   <div class="row">
      <div class="col-md-8">
         <form role="form" class="form-horizontal" action="" enctype="multipart/form-data" action="{{ route('patch_settings_invoice') }}" method="post" autocomplete="off" >
            {{ csrf_field()  }}
            {{ method_field('PATCH') }}
            <div class="form-group">
               <label>@lang('form.terms_and_condition')</label>             
               <textarea  rows="8" class="form-control form-control-sm {{ showErrorClass($errors ,'terms_invoice') }}" name="terms_invoice">{{ old_set('terms_invoice', NULL, $rec) }}</textarea>
               <div class="invalid-feedback">{{ showError($errors, 'terms_invoice') }}</div>
            </div>
            <?php echo bottom_toolbar(); ?>
         </form>
      </div>
      <div class="col-md-4">
      </div>
   </div>
</div>
@endsection