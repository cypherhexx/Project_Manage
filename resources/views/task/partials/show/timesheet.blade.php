<style>
    .hourselect{
        z-index: 1100 !important;
    }
</style>

<br>

<!-- Modal -->
<div id="timeSheetModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">@lang('form.timesheet')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="timeSheetModalForm" action="" method="POST">
                    {{ csrf_field()  }}

                    <input type="hidden" name="id" value="">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>@lang('form.start_time') <span class="required">*</span></label>
                            <input type="text" class="form-control form-control-sm dateTimePicker" name="start_time">
                            <div class="invalid-feedback d-block start_time"></div>

                        </div>
                        <div class="form-group col-md-6">
                            <label>@lang('form.end_time') <span class="required">*</span></label>
                            <input type="text" class="form-control form-control-sm dateTimePicker" name="end_time">
                            <div class="invalid-feedback d-block end_time"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <input type="hidden" name="task_id" value="{{ $rec->id }}">

                        <div class="form-group col-md-6">
                            <label>@lang('form.member') <span class="required">*</span></label>
                            <?php
                            echo form_dropdown('user_id', $data['assigned_to_list'] , '', "class='form-control form-control-sm  selectPickerWithoutSearch'");
                            ?>
                            <div class="invalid-feedback d-block user_id"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>@lang('form.note')</label>
                        <textarea class="form-control form-control-sm" rows="2" name="note"></textarea>
                        <div class="invalid-feedback d-block note"></div>
                    </div>



                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('form.close')</button>
                <button type="button" class="btn btn-primary" id="submitForm">@lang('form.submit')</button>

            </div>
        </div>
    </div>
</div>

<table class="table table-striped table-bordered" cellspacing="0" width="100%" id="timesheet">
    <thead>
    <tr>
        <th>@lang("form.member")</th>
        <th style="display: none;">@lang("form.task")</th>
        <th>@lang("form.start_time")</th>
        <th>@lang("form.end_time")</th>
        <th>@lang("form.note")</th>
        <th>@lang("form.time")(@lang("form.h"))</th>
        <th>@lang("form.time")(@lang("form.decimal"))</th>
        <th>@lang("form.options")</th>

    </tr>
    </thead>
</table>


@section('innerPageJS')
    <script>
        $(function() {

            var dataTable = $('#timesheet').DataTable({

                dom: 'B<"toolbar">frtip',
                initComplete: function(){
                  $("div.toolbar")
                     .html(' <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#timeSheetModal">{{ __("form.new_entry") }}</button>');           
                },  

                // dom: 'Bfrtip',
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
                pageLength: 10,
                ordering: false,
                "columnDefs": [
                    // { className: "text-right", "targets": [2,4] },
                    // { className: "text-center", "targets": [5] }
                    { "targets": [1], "visible": false  }
                
                
                ],
                "ajax": {
                    "url": '{!! route("datatables_timesheet") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {
                        d.task_id = "{{ $rec->id }}";
                       
                        // d.custom = $('#myInput').val();
                        // etc
                    }

                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });

            var modalWindow = $('#timeSheetModal');

            modalWindow.on('shown.bs.modal', function () {


                $('.selectPickerWithoutSearch').select2( {
                    theme: "bootstrap",

                    minimumResultsForSearch: -1,
                    placeholder: function(){
                        $(this).data('placeholder');
                    },
                    maximumSelectionSize: 6
                } );


                $('.dateTimePicker').daterangepicker({
                    parentEl: "#timeSheetModal",
                    singleDatePicker: true,
                    timePicker: true,

                    locale: {
                        format: 'DD-MM-YYYY hh:mm A'
                    }


                });

            });


            modalWindow.on('hidden.bs.modal', function () {

                $('input[name=id]').val();
                $('.invalid-feedback').html("");
            });


            $('#submitForm').click(function (e) {
                e.preventDefault();

                var id = $('input[name=id]').val();

                var url = (id) ? "{{ route("update_time_sheet") }}" : "{{ route("add_time_sheet") }}";

                $.post( url , $('#timeSheetModalForm').serialize() )
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


        $(document).on('click','.edit_item',function(e){
            //  $(this) = your current element that clicked.
            // additional code
            e.preventDefault();
            var id = $(this).data('id');

            $.post( "{{ route("get_time_sheet_information") }}", { "_token": "{{ csrf_token() }}", timesheet_id : id})
                .done(function( response ) {

                    if(response.status == 1)
                    {

                        var obj = response.data;
                        $('input[name=id]').val(obj.id);
                        $('input[name=start_time]').val(obj.start_time);
                        $('input[name=end_time]').val(obj.end_time);
                        $('select[name=task_id]').val(obj.task_id);
                        $('select[name=user_id]').val(obj.user_id);
                        $('textarea[name=note]').val(obj.note);

                        $('#timeSheetModal').modal('show');


                    }
                    else
                    {

                    }
                });


        });
    </script>
@endsection