@extends('setup.index')
@section('title', __('form.settings') . " : " .__('form.pusher.com'))
@section('setting_page')
<div class="main-content">
   <form role="form" class="form-horizontal"  enctype="multipart/form-data" action="{{ route('patch_settings_pusher') }}" method="post" autocomplete="off" >
      {{ csrf_field()  }}
      {{ method_field('PATCH') }}
      <h5>@lang('form.pusher.com')</h5>
      <hr>
      <div class="row">
         <div class="col-md-6">
            <div class="form-group">
               <div class="custom-control custom-checkbox">
                  <input {{ (old_set('is_enable', NULL, $rec)) ? 'checked' : '' }} type="checkbox" class="custom-control-input" id="customCheck1" name="is_enable" value="1">
                  <label  class="custom-control-label" for="customCheck1" >@lang('form.enable_realtime_notifications')</label>
               </div>
               <div class="clearfix"></div>
            </div>
            <div class="form-group">
               <label>@lang('form.app_id') <span class="required">*</span></label>
               <input type="text" class="form-control form-control-sm  {{ showErrorClass($errors, 'app_id') }}" name="app_id" 
                  value="{{ old_set('app_id', NULL, $rec) }}">
               <div class="invalid-feedback">{{ showError($errors, 'app_id') }}</div>
            </div>
            <div class="form-group">
               <label>@lang('form.app_key') <span class="required">*</span></label>
               <input type="text" class="form-control form-control-sm  {{ showErrorClass($errors, 'app_key') }}" name="app_key" 
                  value="{{ old_set('app_key', NULL, $rec) }}">
               <div class="invalid-feedback">{{ showError($errors, 'app_key') }}</div>
            </div>
            <div class="form-group">
               <label>@lang('form.app_secret') <span class="required">*</span></label>
               <input type="text" class="form-control form-control-sm  {{ showErrorClass($errors, 'app_secret') }}" name="app_secret"  value="{{ old_set('app_secret', NULL, $rec) }}">
               <div class="invalid-feedback">{{ showError($errors, 'app_secret') }}</div>
            </div>
            <div class="form-group">
               <label>
               @lang('form.app_cluster') 
               <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="@lang('form.pusher_cluster_form_note')" data-original-title="" title=""></i> 
               <a href="https://pusher.com/docs/clusters" target="_blank">https://pusher.com/docs/clusters</a>
               </label>
               <input type="text" class="form-control form-control-sm  {{ showErrorClass($errors, 'app_cluster') }}" name="app_cluster"  value="{{ old_set('app_cluster', NULL, $rec) }}">
               <div class="invalid-feedback">{{ showError($errors, 'app_cluster') }}</div>
            </div>
            <?php echo bottom_toolbar(); ?>
         </div>
      </div>
   </form>
</div>
@endsection