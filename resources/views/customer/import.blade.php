@extends('layouts.main')
@section('title', __('form.customers') .' : '. __('form.import'))
@section('content')
<div class="main-content">
   <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.import_customers')</h5>
      </div>
      <div class="col-md-6">
         <a href="{{ route('download_sample_customer_import_file') }}" class="btn btn-success btn-sm float-md-right">@lang('form.download_sample')</a>
      </div>
   </div>
   <hr>
   <div style="font-size: 13px;">
      <p>@lang('form.import_csv_line_1')</p>
      <p>@lang('form.import_csv_line_2')</p>
      <br>
   </div>
   <div class="table-responsive">
      <table class="table" style="font-size: 12px; ">
         <thead>
            <tr>
               <th class="bold"><span class="text-danger">*</span> @lang('form.first_name')                  
                  <span class="text-info">@lang('form.contact_field')</span>
               </th>
               <th class="bold"><span class="text-danger">*</span> @lang('form.last_name')          
                  <span class="text-info">@lang('form.contact_field')</span>
               </th>
               <th class="bold"><span class="text-danger">*</span> @lang('form.email')                    
                  <span class="text-info">@lang('form.contact_field')</span>
               </th>
               <th class="bold"> @lang('form.contact_phone')                   
                  <span class="text-info">@lang('form.contact_field')</span>
               </th>
               <th class="bold"> @lang('form.position')                   
                  <span class="text-info">@lang('form.contact_field')</span>
               </th>
               <th class="bold"> <span class="text-danger">* </span>@lang('form.company_name') </th>
               <th class="bold"> @lang('form.phone')</th>
               <th class="bold"> @lang('form.vat')</th>
               <th class="bold"> @lang('form.website')</th>
               <th class="bold"> @lang('form.address')</th>
               <th class="bold"> @lang('form.city')</th>
               <th class="bold"> @lang('form.state')</th>
               <th class="bold"> @lang('form.zip_code')</th>
               <th class="bold"> @lang('form.country')</th>
               <th class="bold">  @lang('form.shipping_address')</th>
               <th class="bold">  @lang('form.shipping_city')</th>
               <th class="bold">  @lang('form.shipping_state')</th>
               <th class="bold">  @lang('form.shipping_zip_code')</th>
               <th class="bold">  @lang('form.shipping_country')</th>
               <!--      <th class="bold">  @lang('form.latitude')</th>
                  <th class="bold">  @lang('form.longitude')</th>            
                  <th class="bold">  @lang('form.stripe_id')</th> -->
            </tr>
         </thead>
         <tbody>
            <tr>
               @for($i = 1; $i <= 19; $i++)
               <td>@lang('form.sample_data')</td>
               @endfor                        
            </tr>
         </tbody>
      </table>
      <div style="clear:both;"></div>
   </div>
   <br>
   @if($validation_errors = session('validation_errors'))
   <div class="alert alert-danger" role="alert">@lang('form.import_failed_message')</div>
   @foreach($validation_errors as $er)
   <div class="text-danger" style="font-size: 13px;">{{ $er }}</div>
   @endforeach
   @endif  
   <br>
   @if(Session::has('download_file_to_see_unimported_rows'))
      <p class="alert {{ Session::get('alert-class', 'alert-info') }}"><?php echo Session::get('download_file_to_see_unimported_rows'); ?></p>
   @endif
   <form method="post" action='' enctype="multipart/form-data">
      {{ csrf_field()  }}
      <div class="form-row">
         <div class="form-group col-md-4">
            <label for="group_id">@lang('form.group_id')</label>
            <div class="select2-wrapper">
               <?php echo form_dropdown("group_id[]", $data['group_id_list'], old_set("group_id", NULL, $rec), "class='form-control form-control-sm select2-multiple' multiple='multiple'") ?>
            </div>
            <div class="invalid-feedback">@php if($errors->has('group_id')) { echo $errors->first('group_id') ; } @endphp</div>
         </div>
         <div class="form-group col-md-4">
            <label>@lang('form.password') </label>
            <input type="password" class="form-control form-control-sm" name="password">
            <div class="invalid-feedback">@php if($errors->has('password')) { echo $errors->first('password') ; } @endphp
            </div>
         </div>
      </div>
      <div class="form-group">
         <label>@lang('form.select_file')</label>
         <input type="file" class="form-control-file" name="file">
         <div class="invalid-feedback d-block">@php if($errors->has('file')) { echo $errors->first('file') ; } @endphp</div>
      </div>
      <?php echo bottom_toolbar(__('form.import')); ?>
   </form>
</div>
@endsection