@extends('setup.index')
@section('title', __('form.settings') . " : " .__('form.general_information'))
@section('setting_page')
<form role="form" class="form-horizontal" enctype="multipart/form-data" action="{{ route('patch_company_information') }}" method="post" autocomplete="off" >
   {{ csrf_field()  }}
   {{ method_field('PATCH') }}
   <div class="main-content">
      <h5>@lang('form.general_information')</h5>
      <hr>
      <div class="row">
         <div class="col-md-6">
            <div class="form-group">
               <label>@lang('form.company_name') <span class="required">*</span></label>
               <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_name') ? ' is-invalid' : '' !!}" name="settings[company_name]" value="{{ old_set('company_name', NULL, $rec) }}">
               <div class="invalid-feedback">@php if($errors->has('settings.company_name')) { echo $errors->first('settings.company_name') ; } @endphp</div>
            </div>
            <div class="form-row">
               <div class="form-group col-md-6">
                  <label>@lang('form.phone') <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_phone') ? ' is-invalid' : '' !!}" name="settings[company_phone]" value="{{ old_set('company_phone', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_phone')) { echo $errors->first('settings.company_phone') ; } @endphp</div>
               </div>
               <div class="form-group col-md-6">
                  <label>@lang('form.email') <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_email') ? ' is-invalid' : '' !!}" name="settings[company_email]" value="{{ old_set('company_email', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_email')) { echo $errors->first('settings.company_email') ; } @endphp</div>
               </div>
            </div>
            <div class="form-group">
                  <label>@lang('form.vat_number')</label>
                  <input type="text" class="form-control form-control-sm" name="settings[company_vat_number]" value="{{ old_set('company_vat_number', NULL, $rec) }}">
               </div>
            <div class="form-row">
               <div class="form-group col-md-4">
                  <label>@lang('form.decimal_symbol') <span class="required">*</span></label>
                  <?php
                     echo form_dropdown('settings[decimal_symbol]', $data['dropdowns']['decimal_symbol'], old_set('decimal_symbol', NULL, $rec) , "class='form-control form-control-sm selectPickerWithoutSearch'");
                     ?>
               </div>
               <div class="form-group col-md-4">
                  <label>@lang('form.thousand_seperator') <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.digit_grouping_symbol') ? ' is-invalid' : '' !!}" name="settings[digit_grouping_symbol]" value="{{ old_set('digit_grouping_symbol', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.digit_grouping_symbol')) { echo $errors->first('settings.digit_grouping_symbol') ; } @endphp</div>
               </div>

               <div class="form-group col-md-4">
                  <label>@lang('form.digit_grouping') <span class="required">*</span></label>
                  <?php
                     echo form_dropdown('settings[digit_grouping_method]', $data['dropdowns']['list_of_digit_grouping_methods'], old_set('digit_grouping_method', NULL, $rec) , "class='form-control form-control-sm selectPickerWithoutSearch'");
                     ?>
               </div>
            </div>
            <div class="form-row">
               <div class="form-group col-md-6">
                  <label>@lang('form.time_zone') <span class="required">*</span></label>
                  <?php
                     echo form_dropdown('settings[time_zone]', $data['dropdowns']['time_zone'], old_set('time_zone', NULL, $rec) , "class='form-control form-control-sm selectpicker' data-live-search='true'");
                     ?>
               </div>
               <div class="form-group col-md-6">
                  <label>@lang('form.language') <span class="required">*</span></label>
                  <?php
                     echo form_dropdown('settings[default_language]', $data['dropdowns']['languages'], old_set('default_language', NULL, $rec) , "class='form-control form-control-sm selectPickerWithoutSearch '");
                     ?>
               </div>
            </div>

            <?php
              if(property_exists($rec, 'disable_job_queue'))
              {
                $status_disable_job_queue = (old_set('disable_job_queue', NULL, $rec)) ? 'checked' : '';
              }
              else
              {
                $status_disable_job_queue = (config('queue.default') == 'sync') ? 'checked' : '';
              }
            ?>

            <div class="form-group">
               <div class="custom-control custom-checkbox">
                 <input type="checkbox" class="custom-control-input" id="disable_job_queue"  name="settings[disable_job_queue]" value="1"
                 {{ $status_disable_job_queue }}
                 >
                 <label class="custom-control-label" for="disable_job_queue">@lang('form.disable_job_queue')</label>
               </div>
            </div>



            <?php echo bottom_toolbar(); ?>
         </div>
         <div class="col-md-6">

            

            <div class="form-group">
               <label>@lang('form.address') <span class="required">*</span></label>
               <textarea class="form-control form-control-sm {!! $errors->has('settings.company_address') ? ' is-invalid' : '' !!}" rows="4" name="settings[company_address]">{{ old_set('company_address', NULL, $rec) }}</textarea>
               <div class="invalid-feedback">@php if($errors->has('settings.company_address')) { echo $errors->first('settings.company_address') ; } @endphp</div>
            </div>
            <div class="form-row">
               <div class="form-group col-md-6">
                  <label>@lang('form.city') <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_city') ? ' is-invalid' : '' !!}" name="settings[company_city]" value="{{ old_set('company_city', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_city')) { echo $errors->first('settings.company_city') ; } @endphp</div>
               </div>
               <div class="form-group col-md-6">
                  <label>@lang('form.state')</label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_state') ? ' is-invalid' : '' !!}" name="settings[company_state]" value="{{ old_set('company_state', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_state')) { echo $errors->first('settings.company_state') ; } @endphp</div>
               </div>
            </div>
            <div class="form-row">
               <div class="form-group col-md-6">
                  <label>@lang('form.country') <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_country') ? ' is-invalid' : '' !!}" name="settings[company_country]" value="{{ old_set('company_country', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_country')) { echo $errors->first('settings.company_country') ; } @endphp</div>
               </div>
               <div class="form-group col-md-6">
                  <label>@lang('form.zip_code') <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_zip_code') ? ' is-invalid' : '' !!}" name="settings[company_zip_code]" value="{{ old_set('company_zip_code', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_zip_code')) { echo $errors->first('settings.company_zip_code') ; } @endphp</div>
               </div>
            </div> 

          <div class="form-group">
               <label>@lang('form.first_day_of_week') <span class="required">*</span></label>
               <?php
               $days = [
                      __('form.sunday'),
                      __('form.monday'),
                      __('form.tueday'),
                      __('form.wednesday'),
                      __('form.thursday'),
                      __('form.friday'),
                      __('form.saturday'),
                  ];
                  echo form_dropdown('settings[first_day_of_week]', $days , old_set('first_day_of_week', NULL, $rec) , "class='form-control form-control-sm selectPickerWithoutSearch '");
                  ?>
            </div>   



         </div>
      </div>

      @include('setup.google_recaptcha')


      <hr>
      <h5>@lang('form.company_logo')</h5>
      <hr>
      
      <div class="form-group">
          <label >@lang('form.upload_logo') ( @lang('form.company_logo_note') )</label>
          <input type="file" class="form-control-file" name="company_logo">
          <small class="form-text text-muted">@lang('form.logo_upload_note')</small>
          <div class="invalid-feedback d-block">@php if($errors->has('company_logo')) { echo $errors->first('company_logo') ; } @endphp</div>
      </div>

      @if(get_company_logo())
         <img src="{{ get_company_logo() }}">         
      @endif
      <hr>

      <div class="form-group">
          <label >@lang('form.upload_logo') (@lang('form.company_logo_internal_note'))</label>
          <input type="file" class="form-control-file" name="company_logo_internal">
          <small class="form-text text-muted">@lang('form.logo_upload_note'), 
            <span class="text-success">@lang('form.transparent_logo_note')</span></small>
          <div class="invalid-feedback d-block">@php if($errors->has('company_logo_internal')) { echo $errors->first('company_logo_internal') ; } @endphp</div>
      </div>

       @if(get_company_logo(NULL, TRUE))
         <img src="{{ get_company_logo(NULL, TRUE) }}">         
      @endif

      <hr>
      <div class="form-group">
          <label >@lang('form.favicon')</label>
          <input type="file" class="form-control-file" name="favicon">        
          <div class="invalid-feedback d-block">@php if($errors->has('favicon')) { echo $errors->first('favicon') ; } @endphp</div>
      </div>

       @if(get_favicon())
         <img src="{{ get_favicon() }}">         
      @endif

    
      <hr>
      <small class="form-text text-muted float-md-right">Version : {{ get_software_version() }}</small>   
      <div class="clearfix"></div>  
      
   </div>
</form>

@endsection
@section('onPageJs')
@endsection