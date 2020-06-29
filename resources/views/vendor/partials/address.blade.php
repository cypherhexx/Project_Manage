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
     
   </div>
</div>