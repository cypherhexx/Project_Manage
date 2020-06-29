<div class="form-row">
   <div class="form-group col-md-6">
      <label>@lang('form.first_name') (@lang('form.primary_contact')) <span class="required">*</span></label>
      <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_first_name') }}" name="contact_first_name" value="{{ old_set('contact_first_name', NULL, $rec) }}">
      <div class="invalid-feedback">{{ showError($errors, 'contact_first_name') }}</div>
   </div>
   <div class="form-group col-md-6">
      <label>@lang('form.last_name') (@lang('form.primary_contact')) <span class="required">*</span></label>
      <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_last_name') }}" name="contact_last_name" value="{{ old_set('contact_last_name', NULL, $rec) }}">
      <div class="invalid-feedback">{{ showError($errors, 'contact_last_name') }}</div>
   </div>
</div>
<div class="form-row">
   <div class="form-group col-md-6">
      <label>@lang('form.email') (@lang('form.primary_contact')) <span class="required">*</span></label>
      <input type="email" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_email') }}" name="contact_email" value="{{ old_set('contact_email', NULL, $rec) }}" >
      <div class="invalid-feedback">{{ showError($errors, 'contact_email') }}</div>
   </div>
   <div class="form-group col-md-6">
      <label>@lang('form.password') (@lang('form.primary_contact'))</label>
      <div class="input-group input-group-sm">
         <input type="password" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_password') }}" name="contact_password" value="{{ old_set('contact_password', NULL, $rec) }}" id="password">
         <div class="input-group-append">
            <span class="input-group-text"><a  href="#" id="fa-eye"><i class="fas fa-eye"></i></a></span>
            <span class="input-group-text"><a href="#" id="fa-sync"><i class="fas fa-sync"></i></a></span>
         </div>
      </div>
      <div class="invalid-feedback">{{ showError($errors, 'contact_password') }}</div>
   </div>
</div>
<div class="form-row">
   <div class="form-group col-md-6">
      <label>@lang('form.position') (@lang('form.primary_contact'))</label>
      <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_position') }}" 
         name="contact_position"  value="{{ old_set('contact_position', NULL, $rec) }}">
      <div class="invalid-feedback">{{ showError($errors, 'contact_position') }}</div>
   </div>
   <div class="form-group col-md-6">
      <label>@lang('form.phone') (@lang('form.primary_contact'))</label>
      <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'contact_phone') }}" name="contact_phone"  value="{{ old_set('contact_phone', NULL, $rec) }}">
      <div class="invalid-feedback">{{ showError($errors, 'contact_phone') }}</div>
   </div>
</div>