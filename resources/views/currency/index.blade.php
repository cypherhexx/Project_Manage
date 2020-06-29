@extends('setup.index')
@section('title', __('form.settings') . " : " . __('form.currencies'))
@section('setting_page')


    <div class="main-content">

        @if($flash = session('error_message'))
            <div class="alert alert-primary" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                {{ $flash }}
            </div>
        @endif


        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">
            @lang('form.new_currency')
        </button>

        <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">@lang('form.currency')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="myModalForm" action="" method="POST">


                            <input type="hidden" name="id" value="">
                            <div class="form-group">
                                <label>@lang('form.iso_code') <span class="required">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="code">
                                <div class="invalid-feedback d-block code"></div>
                            </div>

                            <div class="form-group">
                                <label>@lang('form.symbol') <span class="required">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="symbol">
                                <div class="invalid-feedback d-block symbol"></div>
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
                <th>@lang("form.iso_code")</th>
                <th>@lang('form.symbol')</th>
                <th>@lang('form.base_currency')</th>                
                <th>@lang("form.options")</th>
            </tr>
            </thead>
        </table>
    </div>

<form method="POST" id="form_change_default_currency" action="{{ route("change_default_currency") }}">
    {{ csrf_field()  }}
    <input type="hidden" id="currency" name="id">
</form>
   
@endsection


@section('onPageJs')
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
                // "columnDefs": [
                //     { className: "text-right", "targets": [2,4] },
                //     { className: "text-center", "targets": [5] }
                //
                //
                // ],
                "ajax": {
                    "url": '{!! route("datatables_currencies") !!}',
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




            $('#myModal').on('shown.bs.modal', function () {



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

                var id = $('input[name=id]').val();

                var url = (id) ? "{{ route("patch_currency") }}" : "{{ route("post_currency") }}";

                var postData = $('#myModalForm').serializeArray();
                postData.push({ "name": "_token", "value" : "{{ csrf_token() }}" });



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

            $.post( "{{ route("get_information_currency") }}", { "_token": "{{ csrf_token() }}", id : id})
                .done(function( response ) {
                    if(response.status == 1)
                    {

                        var obj = response.data;
                        $('input[name=id]').val(obj.id);
                        $('input[name=code]').val(obj.code);
                        $('input[name=symbol]').val(obj.symbol);
                        
                        $('#myModal').modal('show');


                    }
                    else
                    {

                    }
                });


        });


        $(document).on('change','.is_default_currency',function(e){

            e.preventDefault();
         
            $("#currency").val($(this).data('id'));

            $("#form_change_default_currency").submit();

            

        });

    </script>
@endsection 

