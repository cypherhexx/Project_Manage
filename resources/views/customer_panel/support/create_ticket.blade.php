@extends('layouts.customer.main')
@section('title', __('form.new_ticket')   )
@section('content')

<style>
.select2-container--bootstrap .select2-results__option--highlighted[aria-selected] {
    background-color: #eee !important;
    color: inherit !important;
}
</style>



<form id="ticketForm" method="post" action="{{ (isset($rec->id)) ? route( 'patch_ticket', $rec->id) : route('cp_post_ticket') }}">
<div class="main-content" style="">
   
    <h5>@lang('form.new_ticket')</h5>
    <hr>
    
      {{ csrf_field()  }}
      @if(isset($rec->id))
      {{ method_field('PATCH') }}
      @endif      

 <div class="form-group">
               <label>@lang('form.subject') <span class="required">*</span> </label>
               <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'subject') }}" name="subject" value="{{ old_set('subject', NULL,$rec) }}">
               <div class="invalid-feedback">{{ showError($errors, 'subject') }}</div>
            </div>


        
         <div class="form-row">

           

                <div class="form-group col-md-4">
                      <label>@lang('form.department') <span class="required">*</span></label>
                      <?php
                         echo form_dropdown('department_id', $data['department_id_list'] , old_set('department_id', NULL,$rec), "class='form-control selectPickerWithoutSearch'");
                         
                         ?>
                      <div class="invalid-feedback d-block">{{ showError($errors, 'department_id') }}</div>
                   </div>


                    <div class="form-group col-md-4">
                      <label>@lang('form.priority') <span class="required">*</span></label>
                      <?php
                         echo form_dropdown('ticket_priority_id', $data['ticket_priority_id_list'] , old_set('ticket_priority_id', NULL,$rec), "class='form-control selectPickerWithoutSearch'");
                         
                         ?>
                      <div class="invalid-feedback d-block">{{ showError($errors, 'ticket_priority_id') }}</div>
                   </div>


                    <div class="form-group col-md-4">
                  <label>@lang('form.project') <span class="required">*</span></label>
                  <?php
                     echo form_dropdown('project_id', $data['project_id_list'] , old_set('project_id', NULL,$rec), "class='form-control selectPickerWithoutSearch'");
                     
                     ?>
                  <div class="invalid-feedback d-block">{{ showError($errors, 'project_id') }}</div>
               </div>

       


                </div>



            

     
     <div class="form-group">
  <label>@lang('form.details') <span class="required">*</span></label>
  <textarea name="details" id="details" rows="10" class="form-control form-control-sm">{{ old_set('details', NULL,$rec) }}</textarea>
  <div class="invalid-feedback d-block">{{ showError($errors, 'details') }}</div>
</div>  

<?php upload_button('ticketForm'); ?>

      <div style="text-align: right;">
                    <input type="submit" class="btn btn-primary" value="@lang('form.submit_ticket')"/>

                </div>
</div>



   </form>   


@section('innerPageJs')
   <script type="text/javascript">
     
     $(function(){

        


     });
   </script>

   @endsection

@endsection
@section('onPageJs')


@yield('innerPageJs')
@endsection