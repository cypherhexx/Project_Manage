<div id="datatableFitler">
<div class="form-row">
  <div class="form-group col-md-4">
     <label>@lang('form.status')</label>
     <?php
        echo form_dropdown('status_id', $data['estimate_statuses_id_list'] , $data['default_estimate_status_id_list']  , "class='form-control four-boot' multiple='multiple' ");
        ?>
  </div>
  
</div>
</div>
<hr>