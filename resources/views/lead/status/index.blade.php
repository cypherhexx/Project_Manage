@extends('setup.index')
@section('title', __('form.settings') . " : " .__('form.leads_statuses'))
@section('setting_page')




    <div class="main-content">

 <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">
            @lang('form.new_lead_status')
        </button>

        <!-- Modal -->
        <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">@lang('form.lead_status')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="myModalForm" action="" method="POST" onsubmit="return false;">

                            
                            <input type="hidden" name="id" value="">
                            <div class="form-group">
                                <label>@lang('form.name') <span class="required">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="name">
                                <div class="invalid-feedback d-block name"></div>
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

        <hr>
        <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
            <thead>
            <tr>
                <th>@lang("form.name")</th>
                <th>@lang("form.options")</th>
            </tr>
            </thead>
        </table>
    </div>
@endsection
@section('onPageJs')
    <script>

        $(function() {

            dataTable = $('#data').DataTable({

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
                // "columnDefs": [
                //     { className: "text-right", "targets": [2,4] },
                //     { className: "text-center", "targets": [5] }
                //
                //
                // ],
                "ajax": {
                    "url": '{!! route("datatables_leads_status") !!}',
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



            $('#myModal').on('hidden.bs.modal', function (e) {

                $('.error').html("");
                $("#myModal").find("input[type=text], textarea, input[type=hidden]").val("");
            });

            $( "input[type=text], textarea" ).focus(function() {

                $(this).next('.error').html("");
            });


            $('#submitForm').click(function (e) {
                e.preventDefault();

                var postData = $('#myModalForm').serializeArray();
                postData.push({ "name": "_token", "value" : "{{ csrf_token() }}" });

                var id = $('input[name=id]').val();

                var url = (id) ? "{{ route('patch_lead_status', ':id') }}" : "{{ route('post_lead_status') }}";           
                

                if(id)
                {
                    url = url.replace(':id', id);
                    postData.push({ "name": "_method", "value" : "PATCH" });
                 
                }

                $.post( url , postData )
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

                            $("#myModal").find("input[type=text], textarea").val("");

                            $('#myModal').modal('hide');
                        }
                    });



            });


        });


        $(document).on('click','.edit_item',function(e){
            //  $(this) = your current element that clicked.
            // additional code
            e.preventDefault();
            var id = $(this).data('id');
            var name = $(this).data('name');
       
            $('input[name=id]').val(id);

            $('input[name=name]').val(name);

            $('#myModal').modal('show');


        });


    </script>
@endsection
