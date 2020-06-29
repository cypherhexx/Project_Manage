<div class="form-row">
   <div class="form-group col-md-6">
      <label>@lang('form.related_to')</label>
      <?php
         echo form_dropdown('component_id', $data['component_id_list'], old_set('component_id', NULL,$rec), "class='form-control related_to  selectPickerWithoutSearch'");
         
         ?>
   </div>
   <div class="form-group col-md-6">
      <div class="form-check" style="margin-top: 30px;">
         <input class="form-check-input" name="is_billable" type="checkbox" value="1" {{ (old_set('is_billable', NULL,$rec)) ? 'checked' : '' }}>
         <label class="form-check-label" for="defaultCheck1">
         @lang('form.is_billable')
         </label>
      </div>
   </div>
</div>
<?php
   $component_id = old_set('component_id', NULL,$rec);
   ?>
<div class="form-group" style="{{ ($component_id) ? '' : 'display:none' }}">
   <label for="component_number"></label>
   <?php
      $errorClass = ($errors->has('component_number')) ? 'is-invalid' : '';
      echo form_dropdown('component_number', $data['component_number_options'], old_set('component_number', NULL,$rec), "class='form-control  component_number $errorClass '");
      
      ?>
   <div class="invalid-feedback">@php if($errors->has('component_number')) { echo $errors->first('component_number') ; } @endphp</div>
</div>
<div class="form-row" id="milestone_id" style="{{ ($component_id == COMPONENT_TYPE_PROJECT) ? '' : 'display: none;' }}">
   <div class="form-group col-md-4">
      <label for="milestone_id">@lang('form.milestone')</label>
      <?php
         $errorClass = ($errors->has('milestone_id')) ? 'is-invalid' : '';
         echo form_dropdown('milestone_id', $data['milestone_options'], old_set('milestone_id', NULL,$rec), "class='form-control  milestone_id $errorClass '");
         
         ?>
      <div class="invalid-feedback">@php if($errors->has('milestone_id')) { echo $errors->first('milestone_id') ; } @endphp</div>
   </div>
</div>
<div class="form-row">
   <div class="form-group col-md-6">
      <label>@lang('form.hourly_rate') </label>
      <input type="text" class="form-control form-control-sm  @php if($errors->has('hourly_rate')) { echo 'is-invalid'; } @endphp" name="hourly_rate" value="{{ old_set('hourly_rate', NULL,$rec) }}">
      <div class="invalid-feedback">@php if($errors->has('hourly_rate')) { echo $errors->first('hourly_rate') ; } @endphp</div>
   </div>
   <div class="form-group col-md-6">
      <label>@lang('form.priority')</label>
      <?php
         echo form_dropdown('priority_id', $data['priority_id_list'] , old_set('priority_id', NULL,$rec), "class='form-control  selectPickerWithoutSearch '");
         ?>
   </div>
</div>
<div class="form-row">
   <div class="form-group col-md-6">
      <label>@lang('form.start_date') </label>     
      <input type="text" class="form-control form-control-sm initially_empty_datepicker  @php if($errors->has('start_date')) { echo 'is-invalid'; } @endphp" name="start_date" value="{{ sql2date(old_set('start_date', NULL,$rec) ) }}">
      <div class="invalid-feedback">@php if($errors->has('start_date')) { echo $errors->first('start_date') ; } @endphp</div>
   </div>
   <div class="form-group col-md-6">
      <label>@lang('form.due_date') </label>
      <input type="text" class="form-control form-control-sm initially_empty_datepicker  @php if($errors->has('due_date')) { echo 'is-invalid'; } @endphp" name="due_date" value="{{ sql2date(old_set('due_date', NULL,$rec)) }}">
      <div class="invalid-feedback">@php if($errors->has('due_date')) { echo $errors->first('due_date') ; } @endphp</div>
   </div>
</div>
<div class="form-row">
   <div class="form-group col-md-6">
      <label>@lang('form.status')</label>
      <?php
         echo form_dropdown('status_id', $data['status_id_list'] , old_set('status_id', NULL,$rec), "class='form-control  selectPickerWithoutSearch'");
         ?>
   </div>
   <div class="form-group col-md-6">
      <label for="sub_task_id">@lang('form.parent_task')</label>
      <?php
         $errorClass = ($errors->has('parent_task_id')) ? 'is-invalid' : '';
         echo form_dropdown('parent_task_id', $data['parent_task_id_list'], old_set('parent_task_id', NULL,$rec), "class='form-control  parent_task_id $errorClass '");
         
         ?>
      <div class="invalid-feedback">@php if($errors->has('parent_task_id')) { echo $errors->first('parent_task_id') ; } @endphp</div>
   </div>
</div>
<div class="form-group ">
   <label>@lang('form.assigned_to')</label>
   <?php
      echo form_dropdown('assigned_to', $data['assigned_to_list'] , old_set('assigned_to', NULL,$rec), "class='form-control  selectpicker'");
      ?>
</div>
<div class="form-group">
   <label for="group_id">@lang('form.tag')</label>
   <div class="select2-wrapper">
      <?php echo form_dropdown("tag_id[]", $data['tag_id_list'], old_set("tag_id", NULL, $rec), "class='form-control select2-multiple' multiple='multiple'") ?>
   </div>
   <div class="invalid-feedback">@php if($errors->has('tag_id')) { echo $errors->first('group_id') ; } @endphp</div>
</div>