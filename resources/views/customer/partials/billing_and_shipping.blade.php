<div class="row">
   <div class="col-md-6">
      <div style="padding: 10px;">
         <div class="form-group">
            <label for="address">@lang('form.billing_address')</label>
            <textarea id="address" name="address" placeholder="{{ __('form.street') }}" class="form-control form-control-sm " >{{ old_set('address', NULL, $rec) }}</textarea>
            <div class="invalid-feedback">@php if($errors->has('address')) { echo $errors->first('address') ; } @endphp</div>
         </div>
         <div class="form-row">
            <div class="form-group col-md-6">
               <input type="text" class="form-control form-control-sm " placeholder="{{ __('form.city') }}" name="city" value="{{ old_set('city', NULL, $rec) }}">
               <div class="invalid-feedback">@php if($errors->has('city')) { echo $errors->first('city') ; } @endphp</div>
            </div>
            <div class="form-group col-md-6">
               <input type="text" class="form-control form-control-sm " placeholder="{{ __('form.state') }}" name="state" value="{{ old_set('state', NULL, $rec) }}">
               <div class="invalid-feedback">@php if($errors->has('state')) { echo $errors->first('state') ; } @endphp</div>
            </div>
         </div>
         <div class="form-row">
            <div class="form-group col-md-6">
               <input type="text" class="form-control form-control-sm " placeholder="{{ __('form.zip_code') }}" name="zip_code" value="{{ old_set('zip_code', NULL, $rec) }}">
               <div class="invalid-feedback">@php if($errors->has('zip_code')) { echo $errors->first('zip_code') ; } @endphp</div>
            </div>
            <div class="form-group col-md-6">
               <div class="select2-wrapper">
                  <?php echo form_dropdown("country_id", $data['country_id_list'], old_set("country_id", NULL, $rec), "class='form-control form-control-sm  selectpicker '") ?>
               </div>
               <div class="invalid-feedback">@php if($errors->has('country_id')) { echo $errors->first('country_id') ; } @endphp</div>
            </div>
         </div>
      </div>
   </div>
   <div class="col-md-6">
      <div style="padding: 10px;">
         <?php $disable = (old_set('shipping_is_same_as_billing', NULL, $rec) == 1)? TRUE: FALSE; ?>
         <div class="form-group">
            <div style="float: left; margin-bottom: 8px;"><label>@lang('form.shipping_address')</label></div>
            <div class="form-check" style="float: left; margin-left: 10px;">
               <input class="form-check-input" id="shipping_is_same_as_billing" type="checkbox" value="1" name="shipping_is_same_as_billing" {{ ($disable) ? 'checked' : '' }}>
               <label class="form-check-label" for="defaultCheck1">
               @lang('form.shipping_is_same_as_billing')
               </label>
            </div>
            <textarea id="shipping_address" name="shipping_address" placeholder="{{ __('form.street') }}" class="form-control form-control-sm " {{ ($disable) ? 'disabled' : '' }}>{{ old_set('shipping_address', NULL, $rec) }}</textarea>
            <div class="invalid-feedback">@php if($errors->has('shipping_address')) { echo $errors->first('shipping_address') ; } @endphp</div>
         </div>
         <div class="form-row">
            <div class="form-group col-md-6">
               <input type="text" class="form-control form-control-sm " placeholder="{{ __('form.city') }}" name="shipping_city" value="{{ old_set('shipping_city', NULL, $rec) }}" {{ ($disable) ? 'disabled' : '' }}>
               <div class="invalid-feedback">@php if($errors->has('shipping_city')) { echo $errors->first('shipping_city') ; } @endphp</div>
            </div>
            <div class="form-group col-md-6">
               <input type="text" class="form-control form-control-sm " placeholder="{{ __('form.state') }}" name="shipping_state" value="{{ old_set('shipping_state', NULL, $rec) }}" {{ ($disable) ? 'disabled' : '' }}>
               <div class="invalid-feedback">@php if($errors->has('shipping_state')) { echo $errors->first('shipping_state') ; } @endphp</div>
            </div>
         </div>
         <div class="form-row">
            <div class="form-group col-md-6">
               <input type="text" class="form-control form-control-sm " placeholder="{{ __('form.zip_code') }}" name="shipping_zip_code" value="{{ old_set('shipping_zip_code', NULL, $rec) }}" {{ ($disable) ? 'disabled' : '' }}>
               <div class="invalid-feedback">@php if($errors->has('shipping_zip_code')) { echo $errors->first('shipping_zip_code') ; } @endphp</div>
            </div>
            <div class="form-group col-md-6">
               <div class="select2-wrapper">
                  <?php $shipping_country_disable = ($disable) ? 'disabled' : '' ;?>
                  <?php echo form_dropdown("shipping_country_id", $data['country_id_list'], old_set("shipping_country_id", NULL, $rec), "class='form-control form-control-sm  selectpicker'  {{ $shipping_country_disable }}") ?>
               </div>
               <div class="invalid-feedback">@php if($errors->has('shipping_country_id')) { echo $errors->first('shipping_country_id') ; } @endphp</div>
            </div>
         </div>
      </div>
   </div>
</div>