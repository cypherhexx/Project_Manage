@extends('setup.index')
@section('title', __('form.tags'))
@section('setting_page')


    <div class="main-content">

        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">
            @lang('form.new_tag')
        </button>

        <!-- Modal -->
        <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">@lang('form.tag')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="myModalForm" action="" method="POST">

                            
                            <input type="hidden" name="id" value="">
                            <div class="form-group">
                                <label>@lang('form.name') <span class="required">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="name">
                                <div class="error name"></div>
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
                pageLength: 10,
                ordering: false,
                // "columnDefs": [
                //     { className: "text-right", "targets": [2,4] },
                //     { className: "text-center", "targets": [5] }
                //
                //
                // ],
                "ajax": {
                    "url": '{!! route("datatables_tags") !!}',
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
                var postData = $('#myModalForm').serializeArray();
                postData.push({ "name": "_token", "value" : "{{ csrf_token() }}" });


                if(id)
                {
                    var patch_url   = "{{ route('patch_tag', ':id') }}";

                    var url         = patch_url.replace(':id',id );  
                    postData.push({ "name": "_method", "value" : "PATCH" });                    
                }

                else
                {
                    var url = "{{ route("post_tag") }}";
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
       
            e.preventDefault();
            var id = $(this).data('id');

            var url = "{{ route('get_information_tag', ':id') }}";

            url = url.replace(':id', id ); 


            $.post( url , { "_token": "{{ csrf_token() }}", id : id})
                .done(function( response ) {
                    if(response.status == 1)
                    {

                        var obj = response.data;
                        $('input[name=id]').val(obj.id);

                        $('input[name=name]').val(obj.name);

                        $('#myModal').modal('show');


                    }
                    else
                    {

                    }
                });


        });
    </script>
@endsection