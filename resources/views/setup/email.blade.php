@extends('setup.index')
@section('title', __('form.settings') . " : " .__('form.email'))
@section('setting_page')
<style type="text/css">

   <?php 

   if(old_set('company_email_send_using', NULL, $rec) == 'mailgun') { ?>
      #otherMailConfigInfo{
         display: none;
      }
      #mailgunInfo{
         display: block;
      }
   <?php } else { ?> 
      #mailgunInfo{
         display: none;
      }
      #otherMailConfigInfo{
         display: block;
      }
   <?php } ?>

</style>


   <div class="main-content">
      <form role="form" class="form-horizontal" enctype="multipart/form-data" action="{{ route('patch_settings_email') }}" method="post" autocomplete="off" >
   {{ csrf_field()  }}
   {{ method_field('PATCH') }}

      <h5>@lang('form.email')</h5>
      <hr>
      <div class="row">
         <div class="col-md-6">

            <div class="form-group">
               <div class="custom-control custom-checkbox">
                 <input type="checkbox" class="custom-control-input" id="disable_email"  name="settings[disable_email]" value="1"
                 {{ (old_set('disable_email', NULL, $rec)) ? 'checked' : ''}}
                 >
                 <label class="custom-control-label" for="disable_email">@lang('form.disable_email')</label>
               </div>
            </div>

             <div class="form-group">
                  <label>@lang('form.send_email_using') <span class="required">*</span></label>
                  <?php
                     echo form_dropdown('settings[company_email_send_using]', 
                        $data['dropdowns']['email_sending_options'], old_set('company_email_send_using', NULL, $rec) , "class='form-control form-control-sm selectPickerWithoutSearch email_sending_options' ");
                     ?>
               </div>

            <div id="mailgunInfo">

               <div class="form-row">
                  <div class="form-group col-md-6">
                     <label>@lang('form.mailgun_domain') <span class="required">*</span></label>
                     <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_email_mailgun_domain') ? ' is-invalid' : '' !!}" name="settings[company_email_mailgun_domain]" value="{{ old_set('company_email_mailgun_domain', NULL, $rec) }}">
                     <div class="invalid-feedback">@php if($errors->has('settings.company_email_mailgun_domain')) { echo $errors->first('settings.company_email_mailgun_domain') ; } @endphp</div>
                  </div>
                  <div class="form-group col-md-6">
                     <label>@lang('form.mailgun_key') <span class="required">*</span></label>
                     <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_email_mailgun_key') ? ' is-invalid' : '' !!}" name="settings[company_email_mailgun_key]" value="{{ old_set('company_email_mailgun_key', NULL, $rec) }}">
                     <div class="invalid-feedback">@php if($errors->has('settings.company_email_mailgun_key')) { echo $errors->first('settings.company_email_mailgun_key') ; } @endphp</div>
                  </div>
               </div>

            </div>

            <div class="form-group">
                  <label>@lang('form.email_from_address') <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_email_from_address') ? ' is-invalid' : '' !!}" name="settings[company_email_from_address]" value="{{ old_set('company_email_from_address', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_email_from_address')) { echo $errors->first('settings.company_email_from_address') ; } @endphp</div>
               </div>   

          <div id="otherMailConfigInfo">
            <div class="form-row">
               <div class="form-group col-md-6">
                  <label>@lang('form.smtp_host') <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_email_smtp_host') ? ' is-invalid' : '' !!}" name="settings[company_email_smtp_host]" value="{{ old_set('company_email_smtp_host', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_email_smtp_host')) { echo $errors->first('settings.company_email_smtp_host') ; } @endphp</div>
               </div>
               <div class="form-group col-md-3">
                  <label>@lang('form.smtp_port') <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_email_smtp_port') ? ' is-invalid' : '' !!}" name="settings[company_email_smtp_port]" value="{{ old_set('company_email_smtp_port', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_email_smtp_port')) { echo $errors->first('settings.company_email_smtp_port') ; } @endphp</div>
               </div>
               <div class="form-group col-md-3">
                  <label>@lang('form.email_encryption') <span class="required">*</span></label>
                  <?php
                     echo form_dropdown('settings[company_email_encryption]', [ '' => 'None','ssl' => 'SSL', 'tls' => 'TLS'], old_set('company_email_encryption', NULL, $rec) , "class='form-control form-control-sm selectPickerWithoutSearch' ");
                     ?>
               </div>
            </div>
            
          

            <div class="form-row">
               <div class="form-group col-md-6">
                  <label><i class="fa fa-question-circle" data-toggle="tooltip" data-title="@lang('form.email_settings_username_note')" data-original-title="" title=""></i> @lang('form.smtp_username')</label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_email_smtp_username') ? ' is-invalid' : '' !!}" name="settings[company_email_smtp_username]" value="{{ old_set('company_email_smtp_username', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_email_smtp_username')) { echo $errors->first('settings.company_email_smtp_username') ; } @endphp</div>
               </div>
               <div class="form-group col-md-6">
                  <label>@lang('form.smtp_password') <span class="required">*</span> </label>
                  <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_email_smtp_password') ? ' is-invalid' : '' !!}" name="settings[company_email_smtp_password]" value="{{ old_set('company_email_smtp_password', NULL, $rec) }}">
                  <div class="invalid-feedback">@php if($errors->has('settings.company_email_smtp_password')) { echo $errors->first('settings.company_email_smtp_password') ; } @endphp</div>
               </div>
            </div>
          </div>  

            <!-- <div class="form-group">
               <label>@lang('form.bcc_all_emails_to') </label>
               <input type="text" class="form-control form-control-sm {!! $errors->has('settings.company_email_bcc_all_emails_to') ? ' is-invalid' : '' !!}" name="settings[company_email_bcc_all_emails_to]" value="{{ old_set('company_email_bcc_all_emails_to', NULL, $rec) }}">
               <div class="invalid-feedback">@php if($errors->has('settings.company_email_bcc_all_emails_to')) { echo $errors->first('settings.company_email_bcc_all_emails_to') ; } @endphp</div>
            </div> -->
            <div class="form-group">
               <label>@lang('form.email_signature') </label>
               <textarea  class="form-control form-control-sm {!! $errors->has('settings.email_signature') ? ' is-invalid' : '' !!}" name="settings[email_signature]">{{ old_set('email_signature', NULL, $rec) }}</textarea>
               <div class="invalid-feedback">@php if($errors->has('settings.email_signature')) { echo $errors->first('settings.email_signature') ; } @endphp</div>
            </div>
            <?php echo bottom_toolbar(); ?>
         </div>
         <div class="col-md-6">
            <small class="form-text text-muted">@lang('form.allowed_short_codes') : @[company_name] , @[company_logo]</small>
            <hr>
            <div class="form-group">
               <label>@lang('form.predefined_header')</label>
              
               
              
               <textarea  rows="8" class="form-control form-control-sm {!! $errors->has('settings.email_predefined_header') ? ' is-invalid' : '' !!}" name="settings[email_predefined_header]">{{ old_set('email_predefined_header', NULL, $rec) }}</textarea>
               <div class="invalid-feedback">@php if($errors->has('settings.email_predefined_header')) { echo $errors->first('settings.email_predefined_header') ; } @endphp</div>
            </div>
            <div class="form-group">
               <label>@lang('form.predefined_footer') </label>
               <textarea  rows="8" class="form-control form-control-sm {!! $errors->has('settings.email_predefined_footer') ? ' is-invalid' : '' !!}" name="settings[email_predefined_footer]">{{ old_set('email_predefined_footer', NULL, $rec) }}</textarea>
               <div class="invalid-feedback">@php if($errors->has('settings.email_predefined_footer')) { echo $errors->first('settings.email_predefined_footer') ; } @endphp</div>
            </div>
         </div>
      </div>
    
</form>

<hr>
   <h4>@lang('form.send_test_email')</h4>
   <form autocomplete="off" action="{{ route('send_test_email') }}" method="POST">
      {{ csrf_field()  }}
   <div class="form-group">
   <label class="form-text text-muted" >@lang('form.send_test_email_note')</label>
   <input type="email" class="form-control {!! $errors->has('test_email_address') ? ' is-invalid' : '' !!}" name="test_email_address" placeholder="@lang('form.email_address')">
   <div class="invalid-feedback">@php if($errors->has('test_email_address')) { echo $errors->first('test_email_address') ; } @endphp</div>

   </div>
   <button type="submit" class="btn btn-primary">@lang('form.send')</button>
   </form>



</div>


   
@endsection
@section('onPageJs')
<script>
   $(function() {
   
      $('.email_sending_options').change(function(){

            if($(this).val() == 'mailgun')
            {
               $("#mailgunInfo").show();
               $("#otherMailConfigInfo").hide();
            }
            else
            {
               $("#mailgunInfo").hide();
               $("#otherMailConfigInfo").show();
            }
      });
   
   
   });
   
   
</script>
@endsection