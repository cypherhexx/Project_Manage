<?php 
 
 function task_modal($component_type_id, $component_instance, $hide_billing = NULL)
 {
 	$data = \App\Task::dropdown();
 	
 	$component_number = $component_instance->id;

 	if($component_type_id == COMPONENT_TYPE_PROJECT)
 	{
 		// Here $component_instance == $project;
        $data['milestones_id_list'] = $component_instance->milestones()->orderBy('order', 'ASC')->pluck('name', 'id')->toArray();	
 	} 	

 	?>


  <style>
   .select2-container {
   width: 100% !important;
   padding: 0;
   }
   .select2-multiple{
   width: 100% !important;
   }
</style>
<div  id="taskModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="exampleModalCenterTitle"><?php echo __('form.task');?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <form id="taskModalForm" method="post" action="">
               <?php echo csrf_field() ; ?>
               <input type="hidden" name="id" value="">

               <?php if(!$hide_billing) { ?>
               <div class="form-check">
                  <input class="form-check-input" name="is_billable" type="checkbox" value="1">
                  <label class="form-check-label" for="defaultCheck1">
                   <?php echo __('form.is_billable') ;?>
                  </label>
               </div>
               <hr>
               <?php } ?>


               <div class="form-row">
                <?php 
                // Show If billing has not been asked to hide
                if(!$hide_billing) { ?>

                  <div class="form-group col-md-4">
                     <label for="sub_task_id"><?php echo __('form.parent_task'); ?></label>    
                     <?php                       
                        echo form_dropdown('parent_task_id', [], "", "class='form-control  parent_task_id select2-container'");    
                        ?>
                     <div class="invalid-feedback"></div>
                  </div>
                  <div class="form-group col-md-2">
                     <label for="hourly_rate"><?php echo __('form.hourly_rate');?> </label>
                     <input type="text" class="form-control form-control-sm" name="hourly_rate" value="">
                     <div class="invalid-feedback"></div>
                  </div>

                  <?php } ?>

                  <div class="form-group col-md-<?php echo ($hide_billing) ? '6' : '2' ; ?>">
                     <label><?php echo __('form.priority');?></label>
                     <?php
                        echo form_dropdown('priority_id', $data['priority_id_list'] , "", "class='form-control  selectPickerWithoutSearch '");
                        ?>
                  </div>

                  <?php if($component_type_id == COMPONENT_TYPE_PROJECT) { ?>
 					
                  <div class="form-group col-md-4">
                     <label><?php echo __('form.milestone');?></label>
                     <?php
                        echo form_dropdown('milestone_id', $data['milestones_id_list'] , "", "class='form-control  selectPickerWithoutSearch '");
                        ?>
                  </div>
                  <?php } ?>
               </div>
               <div class="form-group">
                  <label for="title"><?php echo __('form.title');?> <span class="required">*</span> </label>
                  <input type="text" class="form-control form-control-sm" name="title" value="">
                  <div class="invalid-feedback d-block"></div>
               </div>
               <div class="form-group">
                  <label><?php echo __('form.description');?> </label>
                  <textarea class="form-control" name="description" id="description" rows="6"></textarea>
               </div>
               <div class="form-row">
                  <div class="form-group col-md-6">
                     <label><?php echo __('form.status');?></label>
                     <?php
                        echo form_dropdown('status_id', $data['status_id_list'] , "", "class='form-control  selectPickerWithoutSearch'");
                        ?>
                  </div>
                  <div class="form-group col-md-6">
                     <label><?php echo __('form.assigned_to');?></label>
                     <?php
                        echo form_dropdown('assigned_to', $data['assigned_to_list'] ,"", "class='form-control  selectpicker'");
                        ?>
                  </div>
               </div>
               <div class="form-row">
                  <div class="form-group col-md-6">
                     <label for="title"><?php echo __('form.start_date');?></label>
                     <input type="text" class="form-control form-control-sm initially_empty_datepicker" name="start_date" value="">
                     <div class="invalid-feedback d-block"></div>
                  </div>
                  <div class="form-group col-md-6">
                     <label><?php echo __('form.due_date');?> </label>
                     <input type="text" class="form-control form-control-sm initially_empty_datepicker" name="due_date" value="">
                     <div class="invalid-feedback"></div>
                  </div>
               </div>
               <div class="form-group">
                  <label for="group_id"><?php echo __('form.tag');?></label>
                  <div class="select2-wrapper">
                     <?php echo form_dropdown("tag_id[]", $data['tag_id_list'], [], "class='form-control select2-multiple' multiple='multiple' ") ?>
                  </div>
                  <div class="invalid-feedback"></div>
               </div>
               <?php 
                  echo upload_button('taskModalForm');
                  ?>
            </form>
         </div>
         <div class="modal-footer">
            <div class="form-check">
               <input class="form-check-input" name="create_new_checkbox" id="create_new_checkbox" type="checkbox" value="1">
               <label class="form-check-label" for="defaultCheck1">
               <?php echo __('form.submit_and_create_new'); ?>
               </label>
            </div>
            <button type="button" class="btn btn-primary" id="submitForm"> <?php echo __('form.submit'); ?></button>
         </div>
      </div>
   </div>
</div>
	
 	
 	<?php
 }

 function task_table_html()
 {
 	?>

 	<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
        <thead>
        <tr>
            <th><?php echo __("form.task_#");?></th>
            <th><?php echo __("form.name");?></th>
            <th><?php echo __("form.status");?></th>
            <th><?php echo __("form.start_date");?></th>
            <th><?php echo __("form.due_date");?></th>
            <th><?php echo __("form.assigned_to");?></th>
            <th><?php echo __("form.tags");?></th>
            <th><?php echo __("form.priority");?></th>
            <th><?php echo __("form.created_by");?></th>
        </tr>
        </thead>
    </table>


 	<?php
 }

  function task_table_js($component_type_id, $component_number, $dont_show_new_task_btn = NULL)
 {
 	?>

    <script>
        $(function() {

        dataTable = "";
            
        dataTable = $('#data').DataTable({
                dom: "<?php echo ($dont_show_new_task_btn) ? 'Bfrtip' : "B<'toolbar'>frtip" ; ?>",
                initComplete: function(){
                  $("div.toolbar")
                     .html('&nbsp <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#taskModal" ><?php echo __("form.new_task") ; ?></button>');           
                },
                buttons: [

                    {
                        init: function(api, node, config) {
                            $(node).removeClass('btn-secondary')
                        },
                        className: "btn-light btn-sm",
                        extend: 'collection',
                        text: 'Export',
                        buttons: [
                            'copy',
                            'excel',
                            'csv',
                            'pdf',
                            'print'
                        ]
                    }
                ],

                "language": {
                    "lengthMenu": '_MENU_ ',
                    "search": '',
                    "searchPlaceholder": "<?php echo  __('form.search') ; ?>"
                    
                }

                ,
                responsive: true,
                processing: true,
                serverSide: true,
                //iDisplayLength: 5
                pageLength: <?php echo data_table_page_length() ; ?>,
                ordering: false,
                // "columnDefs": [
                //     { className: "text-right", "targets": [2,4] },
                //     { className: "text-center", "targets": [5] }
                //
                //
                // ],
                "ajax": {
                    "url": '<?php echo route("datatables_tasks"); ?>',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '<?php echo  csrf_token() ; ?>'
                    },
                    "data": function ( d ) {
                        d.component_id = "<?php echo $component_type_id; ; ?>";
                        d.component_number = "<?php echo $component_number; ; ?>";
                        
                    }
                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });



            // Modal Stuffs

            $('#taskModal').on('show.bs.modal', function (event) {
 
                    var parent_task_id = $( ".parent_task_id" );

                    parent_task_id.select2( {
                        theme: "bootstrap",
                        minimumInputLength: 2,
                        maximumSelectionSize: 6,
                        placeholder: "<?php echo  __('form.select_and_begin_typing') ; ?>",
                        allowClear: true,
                        dropdownParent: $("#taskModal"),
                        ajax: {
                            url: '<?php echo route("get_parent_tasks")  ; ?>',
                            data: function (term, page) {
                              return {
                                  q: term, // search term
                                  component_id: "<?php echo $component_type_id;  ?>",
                                  component_number : "<?php echo $component_number; ; ?>",
                                  
                                  
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

                            return obj.name ||  obj.text
                        }

                    } );

                

                // TinyMce
                <?php tinyMceJsSript('#description'); ?>
  
                $('input[name=hourly_rate]').keypress(function(evt){
                       
                        evt = (evt) ? evt : window.event;   
                        var charCode = (evt.which) ? evt.which : evt.keyCode;
                        if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
                            evt.preventDefault();
                        } else {
                            return true;
                        }

                });
            
            
        });



       $('#submitForm').click(function (e) {
                e.preventDefault();

                var id = $('input[name=id]').val();

                var url = (id) ? "<?php echo  route('patch_task') ; ?>" : "<?php echo  route('post_task') ; ?>";

                var postData = $('#taskModalForm').serializeArray();
                postData.push({ "name": "_token", "value" : "<?php echo  csrf_token() ; ?>" });
                postData.push({ "name": "component_id", "value" : "<?php echo  $component_type_id ; ?>" });
                postData.push({ "name": "component_number", "value" : "<?php echo  $component_number ; ?>" });
                $(this).prop('disabled', true).text("<?php echo __('form.please_wait') ; ?>");
                $.post( url , postData )
                    .done(function( response ) {
                        if(response.status == 2)
                        {

                            $.each(response.errors, function( index, value ) {
                                
                                parentDiv = $('label[for='+  index  +']').parent();
                                parentDiv.find('.form-control').addClass('is-invalid');
                                parentDiv.find('.invalid-feedback').html(value.join());
                            });


                        }
                        else if(response.status == 3)
                        {

                            $.jGrowl(response.msg, { position: 'bottom-right'});

                        }
                        else if(response.status == 1)
                        {
                            dataTable.draw();

                            $("#taskModal").find("input[type=text], textarea, input[type=hidden]").val("");
                            //$("select").val(null).trigger("change"); 
                            $("#list_of_attachments").html("");
                            $('.attachment').remove();

                            tinyMCE.activeEditor.setContent('');
                          
                              if($("#create_new_checkbox").is(":checked"))
                              {
                                $('input[name=id]').val("");
                                 $.jGrowl(response.msg, { position: 'bottom-right'});
                              }
                              else
                              {
                                  $('#taskModal').modal('hide');
                              }

                         
                        }
                    }).always(function(argument) {
                        $('#submitForm').prop('disabled', false).text("<?php echo __('form.submit') ; ?>");
                    });


            



            });

       

            
            $('.form-control').on('focus', function(){

                    parentDiv = $(this).parent();
                    $(this).removeClass('is-invalid');
                    parentDiv.find('.invalid-feedback').html("");
            });



            
        });

      

    </script>

 	<?php
 }