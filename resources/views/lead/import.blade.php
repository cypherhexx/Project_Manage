@extends('layouts.main')
@section('title', __('form.leads') .' : '. __('form.import'))
@section('content')
<div class="main-content">
   <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.import_leads')</h5>
      </div>
      <div class="col-md-6">
         <a href="{{ route('download_sample_lead_import_file') }}" class="btn btn-success btn-sm float-md-right">@lang('form.download_sample')</a>
      </div>
   </div>
   <hr>
   <div style="font-size: 13px;">
      <p>@lang('form.import_csv_line_1')</p>
      <p>@lang('form.import_csv_line_2')</p>
      <br>
   </div>
   <div class="table-responsive no-dt">
      <table class="table table-hover table-bordered" style="font-size: 13px;">
         <thead>
            <tr>
               <th class="bold"><span class="text-danger">*</span> @lang('form.name')</th>
               <th class="bold"> @lang('form.position') </th>
               <th class="bold"> @lang('form.company_name') </th>
               <th class="bold"> @lang('form.description')</th>
               <th class="bold"> @lang('form.country')</th>
               <th class="bold"> @lang('form.zip_code')</th>
               <th class="bold"> @lang('form.city')</th>
               <th class="bold"> @lang('form.state')</th>
               <th class="bold"> @lang('form.address')</th>
               <th class="bold"> @lang('form.email')</th>
               <th class="bold"> @lang('form.website')</th>
               <th class="bold"> @lang('form.phone')</th>
               <th class="bold"> @lang('form.tags')</th>
            </tr>
         </thead>
         <tbody>
            <tr>
               @for($i = 1; $i <= 13; $i++)
               <td>@lang('form.sample_data')</td>
               @endfor                        
            </tr>
         </tbody>
      </table>
   </div>
   <br>
   @if(Session::has('download_file_to_see_unimported_rows'))
      <p class="alert {{ Session::get('alert-class', 'alert-info') }}"><?php echo Session::get('download_file_to_see_unimported_rows'); ?></p>
   @endif
   <form method="post" action='' enctype="multipart/form-data">
      {{ csrf_field()  }}
      <div class="form-row">
         <div class="form-group col-md-4">
            <label for="lead_status_id">@lang('form.status') <span class="required">*</span></label>
            <?php echo form_dropdown("lead_status_id", $data['lead_status_id_list'], old_set("lead_status_id", NULL, $rec), "class='form-control form-control-sm selectPickerWithoutSearch'") ?>
            <div class="invalid-feedback d-block">@php if($errors->has('lead_status_id')) { echo $errors->first('lead_status_id') ; } @endphp</div>
         </div>
         <div class="form-group col-md-4">
            <label for="lead_source_id">@lang('form.source') <span class="required">*</span></label>
            <?php echo form_dropdown("lead_source_id", $data['lead_source_id_list'], old_set("lead_source_id", NULL, $rec), "class='form-control form-control-sm selectPickerWithoutSearch'") ?>
            <div class="invalid-feedback d-block">@php if($errors->has('lead_source_id')) { echo $errors->first('lead_source_id') ; } @endphp</div>
         </div>
         <div class="form-group col-md-4">
            <label for="assigned_to">@lang('form.assigned_to')</label>
            <?php echo form_dropdown("assigned_to", $data['assigned_to_list'], old_set("assigned_to", NULL, $rec), "class='form-control form-control-sm selectpicker'") ?>
            <div class="invalid-feedback d-block">@php if($errors->has('assigned_to')) { echo $errors->first('assigned_to') ; } @endphp</div>
         </div>
      </div>
      <div class="form-group">
         <label>@lang('form.select_file')</label>
         <input type="file" class="form-control-file" name="file">
         <div class="invalid-feedback d-block">@php if($errors->has('file')) { echo $errors->first('file') ; } @endphp</div>
      </div>
      <?php echo bottom_toolbar(); ?>
   </form>
</div>
@endsection