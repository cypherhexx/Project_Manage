<?php 
 
 function reminder_modal()
 {
 	  $data = \App\Reminder::dropdown();
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
<div id="reminderModal" class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="exampleModalCenterTitle"><?php echo __('form.reminder');?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <form id="reminderModalForm" method="post" action="">
               <?php echo csrf_field() ; ?>
               <input type="hidden" name="id" value="">
               <div class="form-group">
                  <label for="date_to_be_notified"><span class="required">*</span> <?php echo __('form.date_to_be_notified'); ?></label>
                  <input type="text" class="form-control form-control-sm" name="date_to_be_notified">
                  <div class="invalid-feedback d-block date_to_be_notified"></div>
               </div>
               <div class="form-group">
                  <label for="send_reminder_to"><span class="required">*</span> <?php echo __('form.send_reminder_to');?></label>
                  <?php
                     echo form_dropdown('send_reminder_to', $data['remind_to_list'] , "", "class='form-control selectpicker '");
                     ?>
                  <div class="invalid-feedback d-block send_reminder_to"></div>
               </div>
               <div class="form-group">
                  <label for="description"><span class="required">*</span> <?php echo __('form.description');?> </label>
                  <textarea class="form-control" name="description" id="description"></textarea>
                  <div class="invalid-feedback d-block description"></div>
               </div>
            </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="submitForm"> <?php echo __('form.submit'); ?></button>
         </div>
      </div>
   </div>
</div>
	
 	
 	<?php
 }

 function reminder_table_html()
 {
 	?>

 	<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
        <thead>
        <tr>
            <th><?php echo __("form.description");?></th>
            <th><?php echo __("form.date");?></th>
            <th><?php echo __("form.remind");?></th>
            <th><?php echo __("form.is_notified");?></th>
            <th><?php echo __("form.action");?></th>             
        </tr>
        </thead>
    </table>


 	<?php
 }

  function reminder_table_js($remindable_type, $remindable_id, $dont_show_new_reminder_btn = NULL)
 {
 	?>

    <script>
        $(function() {

          $('input[name=date_to_be_notified]').daterangepicker({
            timePicker: true,
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'YYYY-MM-DD hh:mm A'
            },
            startDate: moment().format('YYYY-MM-DD hh:mm A'),
            parentEl: "#reminderModal .modal-body"

          });   

        dataTable = "";
            
        dataTable = $('#data').DataTable({
                dom: "<?php echo ($dont_show_new_reminder_btn) ? 'Bfrtip' : "B<'toolbar'>frtip" ; ?>",
                initComplete: function(){
                  $("div.toolbar")
                     .html('&nbsp <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#reminderModal" ><?php echo __("form.new_reminder") ; ?></button>');           
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
                    "url": '<?php echo route("datatables_reminders"); ?>',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '<?php echo  csrf_token() ; ?>'
                    },
                    "data": function ( d ) {
                        d.remindable_type = "<?php echo $remindable_type; ; ?>";
                        d.remindable_id = "<?php echo $remindable_id; ; ?>";
                        
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


                 // Modal Stuffs

            $('#reminderModal').on('show.bs.modal', function (event) { 
                    

                    $('select[name=send_reminder_to]').select2( {
              
                        dropdownParent: $("#reminderModal"),

                         theme: "bootstrap",
                          placeholder: function(){
                              $(this).data('placeholder');
                          },
                          maximumSelectionSize: 6
                        

                    } );

            });
            
       $('#submitForm').click(function (e) {
                e.preventDefault();

                var id = $('input[name=id]').val();

                var url = (id) ? "<?php echo  route('patch_reminder') ; ?>" : "<?php echo  route('post_reminder') ; ?>";

                var postData = $('#reminderModalForm').serializeArray();
                postData.push({ "name": "_token", "value" : "<?php echo  csrf_token() ; ?>" });
                postData.push({ "name": "remindable_type", "value" : "<?php echo  $remindable_type ; ?>" });
                postData.push({ "name": "remindable_id", "value" : "<?php echo  $remindable_id ; ?>" });
                $(this).prop('disabled', true).text("<?php echo __('form.please_wait') ; ?>");
                $.post( url , postData )
                    .done(function( response ) {
                        if(response.status == 2)
                        {

                            $.each(response.errors, function( index, value ) {
                                
                                parentDiv = $('#reminderModal label[for='+  index  +']').parent();
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

                            $("#reminderModal").find("input[type=text], textarea, input[type=hidden]").val("");
                            $("select").val(null).trigger("change"); 
                            
                           
                          
                              if($("#create_new_checkbox").is(":checked"))
                              {
                                $('input[name=id]').val("");
                                 $.jGrowl(response.msg, { position: 'bottom-right'});
                              }
                              else
                              {
                                  $('#reminderModal').modal('hide');
                              }

                         
                        }
                    }).always(function(argument) {
                        $('#submitForm').prop('disabled', false).text("<?php echo __('form.submit') ; ?>");
                    });


            



            });

       
          $('#reminderModal').on('hidden.bs.modal', function (e) {

                $('.invalid-feedback').html("");
                $("#reminderModal").find("input[type=text], textarea, input[type=hidden]").val("");
            });
            
            $('.form-control').on('focus', function(){

                    parentDiv = $(this).parent();
                    $(this).removeClass('is-invalid');
                    parentDiv.find('.invalid-feedback').html("");
            });


            


            
            
        });


$(document).on('click','.edit_item',function(e){
            //  $(this) = your current element that clicked.
            // additional code
            e.preventDefault();
            var id = $(this).data('id');

            $.post( "<?php echo route('get_reminder_information') ?>", { "_token": "<?php echo csrf_token(); ?>", id : id})
                .done(function( response ) {
                    if(response.status == 1)
                    {

                        var obj = response.data;
                        $('input[name=id]').val(obj.id);
                        $('select[name=send_reminder_to]').val(obj.send_reminder_to);
                        $('textarea[name=description]').val(obj.description);
                        $('input[name=date_to_be_notified]').val(obj.date_to_be_notified);
                        
                        $('#reminderModal').modal('show');


                    }
                    else
                    {

                    }
                });


        });



        
      

    </script>

 	<?php
 }