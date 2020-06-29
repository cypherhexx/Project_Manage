@extends('setup.index')
@section('title', __('form.settings') . " : " .__('form.estimate'))
@section('setting_page')
<div class="main-content">
   <h5>@lang('form.estimate')</h5>
   <hr>
   <div class="row">
      <div class="col-md-8">
         <form role="form" class="form-horizontal" action="" enctype="multipart/form-data" action="{{ route('patch_settings_estimate') }}" method="post" autocomplete="off" >
            {{ csrf_field()  }}
            {{ method_field('PATCH') }}
            <div class="form-group">
               <label>@lang('form.terms_and_condition')</label>             
               <textarea  rows="8" class="form-control form-control-sm {{ showErrorClass($errors ,'terms_estimate') }}" name="terms_estimate">{{ old_set('terms_estimate', NULL, $rec) }}</textarea>
               <div class="invalid-feedback">{{ showError($errors, 'terms_estimate') }}</div>
            </div>
            <?php echo bottom_toolbar(); ?>
         </form>
      </div>
      <div class="col-md-4">
      </div>
   </div>
</div>
@endsection
