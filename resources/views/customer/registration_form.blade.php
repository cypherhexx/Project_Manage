<div class="row">
   <div class="col-md-6">
      <div class="form-group">
         <label>@lang('form.first_name') <span class="required">*</span></label>
         <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_first_name') }}" name="contact_first_name" value="{{ old_set('contact_first_name', NULL, $rec) }}">
         <div class="invalid-feedback">{{ showError($errors, 'contact_first_name') }}</div>
      </div>
      <div class="form-group">
         <label>@lang('form.last_name') <span class="required">*</span></label>
         <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_last_name') }}" name="contact_last_name" value="{{ old_set('contact_last_name', NULL, $rec) }}">
         <div class="invalid-feedback">{{ showError($errors, 'contact_last_name') }}</div>
      </div>
      <div class="form-group">
         <label>@lang('form.email') <span class="required">*</span></label>
         <input type="email" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_email') }}" name="contact_email" value="{{ old_set('contact_email', NULL, $rec) }}" >
         <div class="invalid-feedback">{{ showError($errors, 'contact_email') }}</div>
      </div>
      <div class="form-group">
         <label>@lang('form.position')</label>
         <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_position') }}" 
            name="contact_position"  value="{{ old_set('contact_position', NULL, $rec) }}">
         <div class="invalid-feedback">{{ showError($errors, 'contact_position') }}</div>
      </div>
      <div class="form-group">
         <label>@lang('form.password') <span class="required">*</span></label>
         <input type="password" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_password') }}" name="contact_password" value="{{ old_set('contact_password', NULL, $rec) }}">
         <div class="invalid-feedback">{{ showError($errors, 'contact_password') }}</div>
      </div>
      <div class="form-group">
         <label>@lang('form.repeat_password') <span class="required">*</span></label>
         <input type="password" class="form-control form-control-sm {{ showErrorClass($errors, 'repeat_password') }}" name="repeat_password" value="{{ old_set('repeat_password', NULL, $rec) }}">
         <div class="invalid-feedback">{{ showError($errors, 'repeat_password') }}</div>
      </div>
      <div class="form-group">
         <label>@lang('form.phone')</label>
         <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_phone') }}" name="contact_phone"  value="{{ old_set('contact_phone', NULL, $rec) }}">
         <div class="invalid-feedback">{{ showError($errors, 'contact_phone') }}</div>
      </div>
   </div>
   <div class="col-md-6">
      <div class="form-group ">
         <label for="name">@lang('form.company') @lang('form.name') <span class="required">*</span></label>
         <input type="text" class="form-control form-control-sm @php if($errors->has('name')) { echo 'is-invalid'; } @endphp " name="name" value="{{ old_set('name', NULL, $rec) }}">
         <div class="invalid-feedback">@php if($errors->has('name')) { echo $errors->first('name') ; } @endphp</div>
      </div>
      <div class="form-group">
         <label for="vat_number">@lang('form.vat_number')</label>
         <input type="text" class="form-control form-control-sm " id="vat_number" name="vat_number" value="{{ old_set('vat_number', NULL, $rec) }}">
         <div class="invalid-feedback">@php if($errors->has('vat_number')) { echo $errors->first('vat_number') ; } @endphp</div>
      </div>
      <div class="form-group">
         <label for="phone">@lang('form.phone')</label>
         <input type="text" class="form-control form-control-sm" id="phone" name="phone" value="{{ old_set('phone', NULL, $rec) }}">
         <div class="invalid-feedback">@php if($errors->has('phone')) { echo $errors->first('phone') ; } @endphp</div>
      </div>
      <div class="form-group">
         <label for="website">@lang('form.website')</label>
         <input type="text" class="form-control form-control-sm " id="website" name="website" value="{{ old_set('website', NULL, $rec) }}">
         <div class="invalid-feedback">@php if($errors->has('website')) { echo $errors->first('website') ; } @endphp</div>
      </div>
      <div class="form-group">
         <label for="address">@lang('form.address')</label>
         <textarea id="address" name="address" class="form-control form-control-sm " >{{ old_set('address', NULL, $rec) }}</textarea>
         <div class="invalid-feedback">@php if($errors->has('address')) { echo $errors->first('address') ; } @endphp</div>
      </div>
      <div class="form-group">
         <label for="city">@lang('form.city')</label>
         <input type="text" class="form-control form-control-sm " name="city" value="{{ old_set('city', NULL, $rec) }}">
         <div class="invalid-feedback">@php if($errors->has('city')) { echo $errors->first('city') ; } @endphp</div>
      </div>
      <div class="form-group">
         <label for="state">@lang('form.state')</label>
         <input type="text" class="form-control form-control-sm " name="state" value="{{ old_set('state', NULL, $rec) }}">
         <div class="invalid-feedback">@php if($errors->has('state')) { echo $errors->first('state') ; } @endphp</div>
      </div>
      <div class="form-group">
         <label for="state">@lang('form.zip_code')</label>
         <input type="text" class="form-control form-control-sm " name="zip_code" value="{{ old_set('zip_code', NULL, $rec) }}">
         <div class="invalid-feedback">@php if($errors->has('zip_code')) { echo $errors->first('zip_code') ; } @endphp</div>
      </div>
      <div class="form-group">
         <div class="select2-wrapper">
            <label for="country">@lang('form.country')</label>
            <?php echo form_dropdown("country_id", $data['country_id_list'], old_set("country_id", NULL, $rec), "class='form-control form-control-sm  selectpicker '") ?>
         </div>
         <div class="invalid-feedback">@php if($errors->has('country_id')) { echo $errors->first('country_id') ; } @endphp</div>
      </div>
   </div>
</div>