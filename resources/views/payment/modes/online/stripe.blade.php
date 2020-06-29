<div id="online_payments_stripe_tab">
    <input type="hidden" name="unique_identifier_id" value="stripe">
   <h6>@lang('form.stripe_checkout')</h6>
   <hr>
   <div class="form-row">
      <div class="form-group col-md-4">
         <label for="settings[stripe_label]" class="control-label">@lang('form.label') <span class="required">*</span></label>
         <input type="text"  name="settings[stripe_label]" class="form-control form-control-sm @php if($errors->has('settings.stripe_label')) { echo 'is-invalid'; } @endphp" value="{{ old_set('stripe_label', NULL,$gateway, $old) }}" >
         <div class="invalid-feedback">@php if($errors->has('settings.stripe_label')) { echo $errors->first('settings.stripe_label') ; } @endphp</div>
      </div>
      <div class="form-group col-md-4">
         <label for="settings[stripe_api_secret_key]" class="control-label">@lang('form.stripe_api_secret_key') <span class="required">*</span></label>
         <input type="text" name="settings[stripe_api_secret_key]" class="form-control form-control-sm @php if($errors->has('settings.stripe_api_secret_key')) { echo 'is-invalid'; } @endphp" value="{{ old_set('stripe_api_secret_key', NULL,$gateway, $old) }}" >
         <div class="invalid-feedback">@php if($errors->has('settings.stripe_api_secret_key')) { echo $errors->first('settings.stripe_api_secret_key') ; } @endphp</div>
      </div>
      <div class="form-group col-md-4">
         <label for="settings[stripe_api_publishable_key]" class="control-label">@lang('form.stripe_api_publishable_key') <span class="required">*</span></label>
         <input type="text" id="settings[stripe_api_publishable_key]" name="settings[stripe_api_publishable_key]" class="form-control form-control-sm @php if($errors->has('settings.stripe_api_publishable_key')) { echo 'is-invalid'; } @endphp" value="{{ old_set('stripe_api_publishable_key', NULL,$gateway, $old) }}"  >
         <div class="invalid-feedback">@php if($errors->has('settings.stripe_api_publishable_key')) { echo $errors->first('settings.stripe_api_publishable_key') ; } @endphp</div>
      </div>
   </div>
   <div class="form-group">
      <label for="settings[stripe_description_dashboard]" class="control-label">@lang('form.gateway_dashboard_payment_description')</label>
      <textarea id="settings[stripe_description_dashboard]" name="settings[stripe_description_dashboard]" class="form-control form-control-sm @php if($errors->has('stripe_description_dashboard')) { echo 'is-invalid'; } @endphp" 
         rows="4">{{ old_set('stripe_description_dashboard', NULL,$gateway, $old) }}</textarea>
   </div>
   <div class="form-group">
      <label for="stripe_active" class="control-label clearfix">
      @lang('form.active') </label>
      <div class="radio radio-primary radio-inline">
         <input type="radio" name="settings[stripe_active]" value="1" 
         {{ (old_set('stripe_active', NULL,$gateway, $old) == 1) ? 'checked' : '' }}
         >
         <label>
         @lang('form.yes')  </label>
      </div>
      <div class="radio radio-primary radio-inline">
         <input type="radio" name="settings[stripe_active]" value="0"
         {{ (old_set('stripe_active', NULL,$gateway, $old) != 1) ? 'checked' : '' }}
         >
         <label>@lang('form.no') </label>
      </div>
   </div>
</div>

 <!--  <div class="form-group" app-field-wrapper="settings[stripe_webhook_key]">
        <label for="settings[stripe_webhook_key]" class="control-label">Stripe Checkout Webhook Key</label>
        <input type="text" id="settings[stripe_webhook_key]" name="settings[stripe_webhook_key]" class="form-control form-control-sm @php if($errors->has('date')) { echo 'is-invalid'; } @endphp"  value="">
    </div>
    <p class="mbot15">Secret key to protect your webhook, webhook URL: https://www.site.com/gateways/stripe/webhook/YOUR_WEBHOOK_KEY
        <br><b>[Configure Webhook only if you are using Subscriptions]</b></p> -->
    <!-- <div class="form-group">
        <label for="settings[stripe_currencies]" class="control-label">@lang('form.currencies') ( @lang('form.comma_seperated'))</label>
        <input type="text" id="settings[stripe_currencies]" name="settings[stripe_currencies]" class="form-control form-control-sm @php if($errors->has('settings.stripe_currencies')) { echo 'is-invalid'; } @endphp" value="{{ old_set('stripe_currencies', NULL,$gateway, $old) }}" >
        <div class="invalid-feedback">@php if($errors->has('settings.stripe_currencies')) { echo $errors->first('settings.stripe_currencies') ; } @endphp</div>
    </div> -->
    <!-- <div class="form-group">
        <label for="stripe_allow_primary_contact_to_update_credit_card" class="control-label clearfix">
            Allow primary contact to update stored credit card token? </label>
        <div class="radio radio-primary radio-inline">
            <input type="radio" id="y_opt_1_allow_primary_contact_to_update_credit_card" name="settings[stripe_allow_primary_contact_to_update_credit_card]" value="1" checked="">
            <label for="y_opt_1_allow_primary_contact_to_update_credit_card">
                Yes </label>
        </div>
        <div class="radio radio-primary radio-inline">
            <input type="radio" id="y_opt_2_allow_primary_contact_to_update_credit_card" name="settings[stripe_allow_primary_contact_to_update_credit_card]" value="0">
            <label for="y_opt_2_allow_primary_contact_to_update_credit_card">
                No </label>
        </div>
    </div> -->
    <!-- <div class="form-group">
        <label for="stripe_test_mode_enabled" class="control-label clearfix">
            @lang('form.enable_test_mode') </label>
        <div class="radio radio-primary radio-inline">
       
            <input type="radio" name="settings[stripe_test_mode_enabled]" value="1" 
            {{ (old_set('stripe_test_mode_enabled', NULL,$gateway, $old) == 1) ? 'checked' : '' }}
            >
            <label>
                @lang('form.yes') </label>
        </div>
        <div class="radio radio-primary radio-inline">
            <input type="radio" id="y_opt_2_settings_testing_mode" name="settings[stripe_test_mode_enabled]" value="0"
            {{ (old_set('stripe_test_mode_enabled', NULL,$gateway, $old) != 1 ) ? 'checked' : '' }}
            >
            
            <label for="y_opt_2_settings_testing_mode">
                @lang('form.no') </label>
        </div>
    </div> -->
<!--     <div class="form-group">
        <label for="stripe_default_selected" class="control-label clearfix">
            Selected by default on invoice </label>
        <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[stripe_default_selected]" value="1" 
            {{ (old_set('stripe_default_selected', NULL,$gateway, $old) == 1) ? 'checked' : '' }}
            >
            <label>@lang('form.yes') </label>
                
        </div>
        <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[stripe_default_selected]" value="0" 
            {{ (old_set('stripe_default_selected', NULL,$gateway, $old) != 1) ? 'checked' : '' }}
            >
            <label>@lang('form.no') </label>
                
        </div>
    </div> -->
