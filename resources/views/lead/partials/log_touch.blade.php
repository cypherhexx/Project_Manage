<div class="modal fade" id="logTouchModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">@lang('form.log_touch')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="logTouchForm" autocomplete="off" action="" method="POST">

            <div class="form-row">
              <div class="form-group col-md-4">
                  <label for="lead_status_id">@lang('form.medium') <span class="required">*</span></label>
                  <?php echo form_dropdown("medium", $data['touch_mediums'], [], "class='form-control form-control-sm selectPickerWithoutSearch'") ?>
                  <div class="invalid-feedback d-block medium"></div>
              </div>
              <div class="form-group col-md-4">
                <label for="">@lang('form.date') <span class="required">*</span></label>
                <input type="text" class="form-control form-control-sm" name="date">
                <div class="invalid-feedback d-block date"></div>
              </div>

              <div class="form-group col-md-4">
                <label for="message-text">@lang('form.time') <span class="required">*</span></label>
                <?php echo form_dropdown("time", $data['time'], [], "class='form-control form-control-sm selectPickerWithoutSearch'") ?>

                <div class="invalid-feedback d-block time"></div>
              </div>

           </div> 


           <div class="form-group">
                  <label for="lead_status_id">@lang('form.resolution') <span class="required">*</span></label>
                  <?php echo form_dropdown("resolution", $data['resolutions'], [], "class='form-control form-control-sm selectPickerWithoutSearch'") ?>
                  <div class="invalid-feedback d-block resolution"></div>
              </div>

            <div class="form-group">
                  <label for="lead_status_id">@lang('form.description') </label>
                  <textarea class="form-control form-control-sm" name="description"></textarea>
                  <div class="invalid-feedback d-block description"></div>
              </div>
          
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('form.close')</button>
        <button type="button" class="btn btn-primary" id="submitLogTouchForm">@lang('form.submit')</button>
      </div>
    </div>
  </div>
</div>