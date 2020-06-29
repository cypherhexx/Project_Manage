<div class="form-row">
   <div class="form-group col-md-6">
      <label>@lang('form.insert_pre_defined_reply')</label>
      <?php echo form_dropdown('pre_defined_replies_id', $data['pre_defined_replies_id'] , NULL, "class='form-control form-control-sm selectpicker'");?>
   </div>

   @if(isset($data['knowledge_base_link_list']))
   <div class="form-group col-md-6">
      <label>@lang('form.insert_knowledge_base_link')</label>
      <select name="insert_knowledge_base_link" class="form-control form-control-sm insert_knowledge_base_link"></select>
   </div>
   @endif

</div>
<div class="form-group">
  <textarea name="details" id="details" class="form-control form-control-sm">{{ old_set('details', NULL,$rec) }}</textarea>
  <div class="invalid-feedback d-block">{{ showError($errors, 'details') }}</div>
</div>	
@if(isset($rec->id))
<div style="margin-bottom: 10px;">
<div class="form-row">

<div class="form-group col-md-4">
  <label>@lang('form.change_status')</label>
  <?php
     echo form_dropdown('ticket_status_id', $data['ticket_status_id_list'] , old('ticket_status_id',TICKET_STATUS_ANSWERED), "class='form-control selectPickerWithoutSearch'");
     
     ?>
  <div class="invalid-feedback d-block">{{ showError($errors, 'ticket_status_id') }}</div>
</div>

<div class="form-group col-md-4">
  <label>@lang('form.cc') </label>
  <input type="email" class="form-control form-control-sm {{ showErrorClass($errors, 'email_cc') }}" name="email_cc" value="{{ old_set('email_cc', NULL,$rec) }}">
  <div class="invalid-feedback">{{ showError($errors, 'email_cc') }}</div>
</div>

</div>

<div class="custom-control custom-checkbox">
  <input type="checkbox" class="custom-control-input" id="customCheck1" name="return_to_ticket_list" value="1" checked>
  <label class="custom-control-label" for="customCheck1" >@lang('form.return_to_ticket_list_after_response')</label>
</div>

</div>	

@endif
<?php upload_button('ticketForm'); ?>
