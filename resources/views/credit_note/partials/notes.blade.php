<div class="main-content"  style="margin-top: 20px;" >
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>@lang('form.admin_note')  </label>
            <textarea name="admin_note" class="form-control ">{{ old_set('admin_note', NULL, $rec) }}</textarea>
            <div class="invalid-feedback">@php if($errors->has('admin_note')) { echo $errors->first('admin_note') ; } @endphp</div>
        </div>
        <div class="form-group col-md-6">
            <label>@lang('form.client_note') </label>
            <textarea name="client_note" class="form-control ">{{ old_set('client_note', NULL, $rec) }}</textarea>
            <div class="invalid-feedback">@php if($errors->has('client_note')) { echo $errors->first('client_note') ; } @endphp</div>
        </div>

    </div>

    <div class="form-group">
        <label>@lang('form.terms_and_condition') </label>
        <textarea name="terms_and_condition" class="form-control ">{{ old_set('terms_and_condition', NULL, $rec) }}</textarea>
        <div class="invalid-feedback">@php if($errors->has('terms_and_condition')) { echo $errors->first('terms_and_condition') ; } @endphp</div>
    </div>
</div>