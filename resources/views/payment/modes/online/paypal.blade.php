<h6>@lang('form.paypal')</h6>
<hr>
<input type="hidden" name="unique_identifier_id" value="paypal">
<div class="form-row">
   <div class="form-group col-md-3">
      <label for="settings[paypal_label]" class="control-label">@lang('form.label') <span class="required">*</span></label>
      <input type="text"  name="settings[paypal_label]" class="form-control form-control-sm @php if($errors->has('settings.paypal_label')) { echo 'is-invalid'; } @endphp" value="{{ old_set('paypal_label', NULL ,$gateway, $old) }}" >
      <div class="invalid-feedback">@php if($errors->has('settings.paypal_label')) { echo $errors->first('settings.paypal_label') ; } @endphp</div>
   </div>
   <div class="form-group col-md-3">
      <label for="settings[paypal_username]" class="control-label">@lang('form.paypal_username') <span class="required">*</span></label>
      <input type="text" id="settings[paypal_username]" name="settings[paypal_username]" class="form-control form-control-sm @php if($errors->has('settings.paypal_username')) { echo 'is-invalid'; } @endphp" value="{{ old_set('paypal_username', NULL,$gateway, $old) }}">
      <div class="invalid-feedback">@php if($errors->has('settings.paypal_username')) { echo $errors->first('settings.paypal_username') ; } @endphp</div>
   </div>
   <div class="form-group col-md-3">
      <label for="settings[paypal_password]" class="control-label">@lang('form.paypal_password') <span class="required">*</span></label>
      <input type="text" id="settings[paypal_password]" name="settings[paypal_password]" class="form-control form-control-sm @php if($errors->has('settings.paypal_password')) { echo 'is-invalid'; } @endphp" value="{{ old_set('paypal_password', NULL,$gateway, $old) }}">
      <div class="invalid-feedback">@php if($errors->has('settings.paypal_password')) { echo $errors->first('settings.paypal_password') ; } @endphp</div>
   </div>
   <div class="form-group col-md-3">
      <label for="settings[paypal_signature]" class="control-label">@lang('form.paypal_signature') <span class="required">*</span></label>
      <input type="text" id="settings[paypal_signature]" name="settings[paypal_signature]" class="form-control form-control-sm @php if($errors->has('settings.paypal_signature')) { echo 'is-invalid'; } @endphp" value="{{ old_set('paypal_signature', NULL,$gateway, $old) }}">
   </div>
</div>
<div class="form-group">
   <label for="settings[paypal_description_dashboard]" class="control-label">@lang('form.gateway_dashboard_payment_description')</label>
   <textarea id="settings[paypal_description_dashboard]" name="settings[paypal_description_dashboard]" class="form-control form-control-sm @php if($errors->has('paypal_description_dashboard')) { echo 'is-invalid'; } @endphp" 
      rows="4">{{ old_set('paypal_description_dashboard', NULL,$gateway , $old) }}
   </textarea>
</div>

<div class="form-group">
   <label for="paypal_active" class="control-label clearfix">
   @lang('form.active') </label>
   <div class="radio radio-primary radio-inline">
      <input type="radio" name="settings[paypal_active]" value="1" 
      {{ (old_set('paypal_active', NULL,$gateway , $old) == 1) ? 'checked' : '' }}
      >
      <label>
      @lang('form.yes')  </label>
   </div>
   <div class="radio radio-primary radio-inline">
      <input type="radio" name="settings[paypal_active]" value="0"
      {{ (old_set('paypal_active', NULL,$gateway , $old) != 1) ? 'checked' : '' }}
      >
      <label>@lang('form.no') </label>
   </div>
</div>
