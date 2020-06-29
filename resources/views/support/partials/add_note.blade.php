<div class="main-content" style="margin-bottom: 10px !important">
  <form action="{{ route('ticket_add_note', $rec->id) }}" method="POST" autocomplete="off">
         {{ csrf_field()  }}       
    <div class="form-group">
	  <textarea name="details" class="form-control form-control-sm">{{ old_set('details', NULL,$rec) }}</textarea>
	  <div class="invalid-feedback d-block">{{ showError($errors, 'details') }}</div>
	</div>
	<div class="form-group">
	  <input type="submit" class="btn btn-primary float-md-right" name="submit" value="@lang('form.post')">
	 <div class="clearfix"></div>
	</div>	
  </form>
</div> 