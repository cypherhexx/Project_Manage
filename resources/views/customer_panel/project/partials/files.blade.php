<style>
   #list_of_attachments .btn{
   float: right !important;
   }
</style>
@if(check_customer_project_permission($rec->settings->permissions, 'upload_files'))
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#fileUploadModal">
@lang('form.upload_new_file')
</button>
<hr>
@endif
<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
   <thead>
      <tr>
         <th>@lang("form.name")</th>
         <th>@lang("form.uploaded_by")</th>
         <th>@lang("form.upload_time")</th>
         <th>@lang("form.download")</th>
         <th>@lang("form.options")</th>
      </tr>
   </thead>
</table>
<!-- Modal -->
<div id="fileUploadModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
   <div class="modal-content">
      <div class="modal-header">
         <h5 class="modal-title" id="exampleModalCenterTitle">
            @lang('form.upload_file')
         </h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
         <span aria-hidden="true">&times;</span>
         </button>
      </div>
      <div class="modal-body">
         <form id="fileUploadModalForm" action="" method="POST">
            {{ csrf_field()  }}
            <div>
               <div class="form-group">
                  <label>@lang('form.display_name') <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-sm" name="display_name">
                  <div class="invalid-feedback d-block display_name"></div>
               </div>
               <small id="emailHelp" class="form-text text-muted">@lang('form.max_size_one_mb')</small>
               <div class="invalid-feedback d-block attachment"></div>
               <div class="form-group">
                  <input type="file" name="attachment" id="attachment" data-form-id="#fileUploadModalForm" 
                     data-short-code-input-id="" style="display:none;"/> 
                  <a href="#" class="btn btn-light upload_link"><i class="fas fa-paperclip "></i> @lang('form.upload_attachment')</a>
               </div>
               <div class="text-center" id="uploading_on_progress" style="display: none;">@lang('form.uploading_file_loading_text')</div>
               <ul class="list-group" id="list_of_attachments"></ul>
         </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('form.close')</button>
            <button type="button" class="btn btn-primary" id="submitForm">@lang('form.submit')</button>
         </div>
      </div>
   </div>
</div>

@section('innerPageJS')
    <script>
        $(function() {
            
            var dataTable = $('#data').DataTable({
                dom: 'Bfrtip',
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
                    "searchPlaceholder": "{{ __('form.search') }}"
                    // "paginate": {
                    //     "previous": '<i class="fa fa-angle-left"></i>',
                    //     "next": '<i class="fa fa-angle-right"></i>'
                    // }
                }

                ,
                responsive: true,
                processing: true,
                serverSide: true,
                //iDisplayLength: 5
                pageLength: {{ data_table_page_length() }},
                ordering: false,
        
                "ajax": {
                    "url": '{{ route("cp_project_attachment_datatable", $rec->id) }}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }

                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });

            var modalWindow = $('#fileUploadModal');

            modalWindow.on('shown.bs.modal', function () {

                clear_inputs();
                

            });


            modalWindow.on('hidden.bs.modal', function () {

                clear_inputs();
            });


            $('#submitForm').click(function (e) {
                e.preventDefault();               

                $.post( "{{ route('cp_project_add_attachment', $rec->id ) }}", $('#fileUploadModalForm').serialize() )
                    .done(function( response ) {
                        if(response.status == 2)
                        {
                            $.each(response.errors, function( index, value ) {

                                $('.' + index).html(value.join());
                            });


                        }
                        else
                        {
                            dataTable.draw();
                            modalWindow.modal('hide');
                        }
                    });



            });

        });


        function clear_inputs()
        {
            $('input[name=display_name]').val("");
            $('input[name=attachment').val("");
            $('.invalid-feedback').html("");
            $("#list_of_attachments").html("");
        }

         $(document).on("upload_complete", function(e){
                $(".upload_link").hide();
          });

         $(document).on("tmp_attachment_removed", function(e){
               $(".upload_link").show();
          });
    </script>
@endsection