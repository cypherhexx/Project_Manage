<style>
.select2-container--bootstrap .select2-results__option--highlighted[aria-selected] {
    background-color: #eee !important;
    color: inherit !important;
}
</style>


<form id="ticketForm" method="post" action="{{ (isset($rec->id)) ? route( 'patch_ticket', $rec->id) : route('post_ticket') }}">
<div class="main-content" style="margin-bottom: 10px !important">

<h5>@lang('form.ticket')</h5>
<hr>
<?php 

  $project_id = Request::query('project_id');

  if($project_id)
  {
    $readonly = 'readonly';
    $disabled = 'disabled';
  }
  else
  {
    $readonly = NULL;
    $disabled = NULL;
  }
  
?>
   {{ csrf_field()  }}
   @if(isset($rec->id))
   {{ method_field('PATCH') }}
   @endif

   @if(!(isset($rec->project_id) && $rec->project_id))      
   <div class="custom-control custom-checkbox">
      <input {{ ( (isset($rec->id)) && (!old_set('customer_contact_id', NULL,$rec))) ? 'checked' : '' }} type="checkbox" class="custom-control-input" id="ticket_without_contact" name="ticket_without_contact" value="1">
      <label class="custom-control-label" for="ticket_without_contact">@lang('form.ticket_without_contact')</label>
   </div>
   @endif

   @if($readonly)
    <input type="hidden" name="customer_contact_id" value="{{ old_set('customer_contact_id', NULL,$rec) }}">
    <input type="hidden" name="name" value="{{ old_set('name', NULL,$rec) }}">
    <input type="hidden" name="email" value="{{ old_set('email', NULL,$rec) }}">
    <input type="hidden" name="project_id" value="{{ old_set('project_id', NULL,$rec) }}">
   @endif

   <div class="row">
      <div class="col-md-6">
         <div class="form-group">
            <label>@lang('form.subject') <span class="required">*</span> </label>
            <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'subject') }}" name="subject" value="{{ old_set('subject', NULL,$rec) }}">
            <div class="invalid-feedback">{{ showError($errors, 'subject') }}</div>
         </div>
         <div class="form-group contact">
            <label>@lang('form.contact') <span class="required">*</span></label>
            <?php
               echo form_dropdown('customer_contact_id', $data['customer_contact_id'] , old_set('customer_contact_id', NULL,$rec), "class='form-control' $disabled ");
               
               ?>
            <div class="invalid-feedback d-block">{{ showError($errors, 'customer_contact_id') }}</div>
         </div>
         <div class="form-row">
            <div class="form-group col-md-6">    
               <label>@lang('form.name') <span class="required">*</span> </label>
               <input {{  $readonly }}  type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'name') }}" name="name" value="{{ old_set('name', NULL,$rec) }}" {{ ( (!old_set('ticket_without_contact', NULL,$rec)) || old_set('customer_contact_id', NULL,$rec)) ? 'readonly' : '' }}>
               <div class="invalid-feedback">{{ showError($errors, 'name') }}</div>
            </div>
            <div class="form-group col-md-6">
               <label>@lang('form.email') <span class="required">*</span> </label>
               <input {{  $readonly }} type="email" class="form-control form-control-sm {{ showErrorClass($errors, 'email') }}" name="email" value="{{ old_set('email', NULL,$rec) }}" {{ ( (!old_set('ticket_without_contact', NULL,$rec)) || old_set('customer_contact_id', NULL,$rec)) ? 'readonly' : '' }}>
               <div class="invalid-feedback">{{ showError($errors, 'email') }}</div>
            </div>
         </div>
         <div class="form-row">
            <div class="form-group col-md-6">
               <label>@lang('form.department') <span class="required">*</span></label>
               <?php
                  echo form_dropdown('department_id', $data['department_id_list'] , old_set('department_id', NULL,$rec), "class='form-control selectPickerWithoutSearch'");
                  
                  ?>
               <div class="invalid-feedback d-block">{{ showError($errors, 'department_id') }}</div>
            </div>
            @if(!isset($rec->id))
            <div class="form-group col-md-6">
               <label>@lang('form.cc') </label>
               <input type="email" class="form-control form-control-sm {{ showErrorClass($errors, 'email_cc') }}" name="email_cc" value="{{ old_set('email_cc', NULL,$rec) }}">
               <div class="invalid-feedback">{{ showError($errors, 'email_cc') }}</div>
            </div>
            @endif
         </div>
      </div>
      <div class="col-md-6">
         <div class="form-group">
            <label for="group_id">@lang('form.tag')</label>
            <div class="select2-wrapper">
               <?php echo form_dropdown("tag_id[]", $data['tag_id_list'], old_set("tag_id", NULL, $rec), "class='form-control select2-multiple' multiple='multiple'") ?>
            </div>
            <div class="invalid-feedback">@php if($errors->has('tag_id')) { echo $errors->first('group_id') ; } @endphp</div>
         </div>
         <div class="form-row">
            <div class="form-group col-md-6">
               <label>@lang('form.assign_ticket') <span class="required">*</span></label>
               <?php
                  echo form_dropdown('assigned_to', $data['customer_support_assistant_id_list'] , old_set('assigned_to', NULL,$rec), "class='form-control selectpicker'");
                  
                  ?>
               <div class="invalid-feedback d-block">{{ showError($errors, 'assigned_to') }}</div>
            </div>
            <div class="form-group col-md-6">
               <label>@lang('form.status') <span class="required">*</span></label>
               <?php
                  echo form_dropdown('ticket_status_id', $data['ticket_status_id_list'] , old_set('ticket_status_id', NULL,$rec), "class='form-control selectPickerWithoutSearch'");
                  
                  ?>
               <div class="invalid-feedback d-block">{{ showError($errors, 'ticket_status_id') }}</div>
            </div>
         </div>
         <div class="form-row">
            <div class="form-group col-md-6">
               <label>@lang('form.priority') <span class="required">*</span></label>
               <?php
                  echo form_dropdown('ticket_priority_id', $data['ticket_priority_id_list'] , old_set('ticket_priority_id', NULL,$rec), "class='form-control selectPickerWithoutSearch'");
                  
                  ?>
               <div class="invalid-feedback d-block">{{ showError($errors, 'ticket_priority_id') }}</div>
            </div>
            <div class="form-group col-md-6">
               <label>@lang('form.service')</label>
               <?php
                  echo form_dropdown('ticket_service_id', $data['ticket_service_id_list'] , old_set('ticket_service_id', NULL,$rec), "class='form-control selectPickerWithoutSearch'");
                  
                  ?>
               <div class="invalid-feedback d-block">{{ showError($errors, 'ticket_service_id') }}</div>
            </div>
         </div>


         <?php $project_id = old_set('project_id', NULL, $rec); ?>

         <div class="form-group project_selection" style="{{ ($project_id) ? '' : 'display:none' }}">
             <label>@lang('form.project')</label>
             <?php       
             echo form_dropdown('project_id', $data['project_id_list'], old_set('project_id', NULL, $rec), "class='form-control  project_id' 
              $disabled");

             ?>
             <div class="invalid-feedback">@php if($errors->has('project_id')) { echo $errors->first('project_id') ; } @endphp</div>
         </div>


      </div>
   </div>
</div>
@if(!isset($rec->id))
<div class="main-content">
    @include('support.partials.post_comment')
</div> 
@endif

 <?php echo bottom_toolbar(); ?>
   </form>   


@section('innerPageJs')
   <script type="text/javascript">
     
     $(function(){

        project_id = null;

        var customer_contact_id = $( "select[name=customer_contact_id]" );

            customer_contact_id.select2( {
                theme: "bootstrap",
                minimumInputLength: 2,
                maximumSelectionSize: 6,
                placeholder: "{{ __('form.select_and_begin_typing') }}",
                allowClear: true,
                
                

                ajax: {
                    url: '{{ route("search_customer_contact") }}',
                    data: function (params) {
                        return {
                            search: params.term
                        }


                    },
                    dataType: 'json',
                    processResults: function (data) {
                        //params.page = params.page || 1;
                        // Tranforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: data.results
                            // pagination: {
                            //     more: (params.page * 10) < data.count_filtered
                            // }
                        };
                    }




                },
                escapeMarkup: function(markup) {
                return markup;
              },
                templateResult: function (obj) {
                    if(obj.text)
                    {
                        return obj.text;
                    }
                    return obj.name + ' <span style="font-size:12px;" class="text-primary">' + obj.customer_name + '</span>';
                },
                templateSelection: function (obj) {

                    if(obj && obj.email)
                    {
                        $("input[name=name]").val(obj.name);
                        $("input[name=email]").val(obj.email);
                       
                        <?php if(isset($rec->project_id) && $rec->project_id) { ?>
                          selectProject();
                        <?php } ?>
                        
                    }

                    return obj.name ||  obj.text
                }

            } ).on('select2:select', function(selection){

                <?php if(isset($rec->project_id) && $rec->project_id) { ?>
                $( ".project_id" ).val(null).trigger("change");
                $('.project_selection').show();
                <?php } ?>
                   
            })
            .on('select2:unselect', function(selection){
            
                $('input[name=name]').val("");
                $('input[name=email]').val("");

                <?php if(isset($rec->project_id) && $rec->project_id) { ?>
                $( ".project_id" ).val(null).trigger("change");
                $('.project_selection').hide();
                <?php } ?>

            });      

            

            $("#ticket_without_contact").change(function() {
                
                var name    = $('input[name=name]');
                var email   = $('input[name=email]');

                if(this.checked) 
                {
                    $('.contact').hide();
                    name.attr('readonly', false).val("");
                    email.attr('readonly', false).val("");

                    customer_contact_id.val(null).trigger('change');
                    
                    // $('input:hidden[name=customer_contact_id]').val("");
                    // $('input:hidden[name=name]').val("");
                    // $('input:hidden[name=email]').val("");
                    // $('input:hidden[name=project_id]').val("");

               

                }
                else
                {
                    $('.contact').show();
                    name.attr('readonly', true).val("");
                    email.attr('readonly', true).val("");
                }
            });


     });



            function selectProject() {

                $('.project_selection').show();

                project_id = $( ".project_id" ).select2( {
                    theme: "bootstrap",
                    minimumInputLength: 2,
                    maximumSelectionSize: 6,
                    placeholder: "{{ __('form.select_and_begin_typing') }}",
                    allowClear: true,

                    ajax: {
                        url: '{{ route("get_project_by_customer_contact_id") }}',
                        data: function (params) {
                            return {
                                search: params.term,
                                customer_contact_id:  $('select[name=customer_contact_id]').val()

                            }


                        },
                        dataType: 'json',
                        processResults: function (data) {
                            //params.page = params.page || 1;
                            // Tranforms the top-level key of the response object from 'items' to 'results'
                            return {
                                results: data.results
                                // pagination: {
                                //     more: (params.page * 10) < data.count_filtered
                                // }
                            };
                        }




                    },

                    templateResult: function (obj) {

                        return obj.name;
                    },
                    templateSelection: function (obj) {

                        return obj.name ||  obj.text }

                } );

            }


<?php if(isset($rec->project_id) && $rec->project_id) { ?>
  selectProject();
<?php } ?>     
   </script>

   @endsection