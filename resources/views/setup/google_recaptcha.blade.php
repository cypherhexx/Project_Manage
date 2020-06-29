<hr>
<h5>@lang('form.google_recaptcha')</h5>
<small class="form-text text-muted">@lang('form.google_recaptcha_uses')</small>

<div class="custom-control custom-checkbox">
  <input {{ (old_set('enable_google_recaptcha', NULL, $rec)) ? 'checked' : '' }} type="checkbox" class="custom-control-input" 
  name="settings[enable_google_recaptcha]" value="1" id="enable_google_recaptcha">
  <label class="custom-control-label" for="enable_google_recaptcha">@lang('form.enable_google_recaptcha')</label>
</div>
<hr>


<div class="form-row">
<div class="form-group col-md-6">
   <label>@lang('form.google_recaptcha_secret_key')</label>
   <input type="text" class="form-control form-control-sm {!! $errors->has('settings.google_recaptcha_secret_key') ? ' is-invalid' : '' !!}" name="settings[google_recaptcha_secret_key]" value="{{ old_set('google_recaptcha_secret_key', NULL, $rec) }}">
   <div class="invalid-feedback">@php if($errors->has('settings.google_recaptcha_secret_key')) { echo $errors->first('settings.google_recaptcha_secret_key') ; } @endphp</div>
</div>
<div class="form-group col-md-6">
   <label>@lang('form.google_recaptcha_site_key')</label>
   <input type="text" class="form-control form-control-sm {!! $errors->has('settings.google_recaptcha_site_key') ? ' is-invalid' : '' !!}" name="settings[google_recaptcha_site_key]" value="{{ old_set('google_recaptcha_site_key', NULL, $rec) }}">
   <div class="invalid-feedback">@php if($errors->has('settings.google_recaptcha_site_key')) { echo $errors->first('settings.google_recaptcha_site_key') ; } @endphp</div>
</div>
</div>